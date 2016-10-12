<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use GuzzleHttp\Client;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class PayPalPaymentNotificationVerifier implements PaymentNotificationVerifier {

	/* private */ const ALLOWED_STATUSES = [ 'Completed', 'Processed' ];
	/* private */ const ALLOWED_CURRENCY_CODES = [ 'EUR' ];

	private $httpClient;
	private $config;

	public function __construct( Client $httpClient, array $config ) {
		$this->httpClient = $httpClient;
		$this->config = $config;
	}

	/**
	 * Verifies the request's integrity and reassures with PayPal
	 * servers that the request wasn't tampered with during transfer
	 *
	 * @param array $request
	 *
	 * @throws PayPalPaymentNotificationVerifierException
	 */
	public function verify( array $request ) {
		if ( !$this->matchesReceiverAddress( $request ) ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Payment receiver address does not match',
				PayPalPaymentNotificationVerifierException::ERROR_WRONG_RECEIVER
			);
		}

		if ( !$this->hasAllowedPaymentStatus( $request ) ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Payment status is not supported',
				PayPalPaymentNotificationVerifierException::ERROR_UNSUPPORTED_STATUS
			);
		}

		if ( !$this->hasValidCurrencyCode( $request ) ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Unsupported currency',
				PayPalPaymentNotificationVerifierException::ERROR_UNSUPPORTED_CURRENCY
			);
		}

		$result = $this->httpClient->post(
			$this->config['base-url'],
			[ 'form_params' => array_merge( [ 'cmd' => '_notify-validate' ], $request ) ]
		);

		if ( $result->getStatusCode() !== 200 ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Payment provider returned an error (HTTP status: ' . $result->getStatusCode() . ')',
				PayPalPaymentNotificationVerifierException::ERROR_VERIFICATION_FAILED
			);
		}

		$responseBody = trim( $result->getBody()->getContents() );
		if ( $responseBody === 'INVALID' ) {
			throw new PayPalPaymentNotificationVerifierException(
				'Payment provider did not confirm the sent data',
				PayPalPaymentNotificationVerifierException::ERROR_VERIFICATION_FAILED
			);
		}

		if ( $responseBody !== 'VERIFIED' ) {
			throw new PayPalPaymentNotificationVerifierException(
				'An error occurred while trying to confirm the sent data',
				PayPalPaymentNotificationVerifierException::ERROR_VERIFICATION_FAILED
			);
		}
	}

	private function matchesReceiverAddress( array $request ): bool {
		return array_key_exists( 'receiver_email', $request ) &&
			$request['receiver_email'] === $this->config['account-address'];
	}

	private function hasAllowedPaymentStatus( array $request ): bool {
		return array_key_exists( 'payment_status', $request ) &&
			in_array( $request['payment_status'], self::ALLOWED_STATUSES );
	}

	private function hasValidCurrencyCode( array $request ): bool {
		return array_key_exists( 'mc_currency', $request ) &&
			in_array( $request['mc_currency'], self::ALLOWED_CURRENCY_CODES );
	}
}
