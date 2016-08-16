<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\PaymentType;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PaymentDataValidator {

	const VIOLATION_AMOUNT_NOT_NUMERIC = 'Amount is not numeric';
	const VIOLATION_AMOUNT_TOO_LOW = 'Amount too low';
	const VIOLATION_UNKNOWN_PAYMENT_TYPE = 'Unknown payment type';

	private $minAmount;

	private $minAmountPerType;

	/**
	 * @param float $minAmount
	 * @param float[] $minAmountPerType keys from the PaymentType enum
	 */
	public function __construct( float $minAmount, array $minAmountPerType = [] ) {
		$this->minAmount = $minAmount;
		$this->minAmountPerType = $minAmountPerType;
	}

	public function validate( $amount, string $paymentType ): ValidationResult {
		if ( !in_array( $paymentType, PaymentType::getPaymentTypes() ) ) {
			return new ValidationResult( new ConstraintViolation( $paymentType, self::VIOLATION_UNKNOWN_PAYMENT_TYPE ) );
		}
		if ( !is_numeric( $amount ) ) {
			return new ValidationResult( new ConstraintViolation( $amount, self::VIOLATION_AMOUNT_NOT_NUMERIC ) );
		}

		if ( $amount < $this->getMinAmountFor( $paymentType ) ) {
			return new ValidationResult( new ConstraintViolation( $amount, self::VIOLATION_AMOUNT_TOO_LOW ) );
		}

		return new ValidationResult();
	}

	private function getMinAmountFor( string $paymentMethod ): float {
		if ( array_key_exists( $paymentMethod, $this->minAmountPerType ) ) {
			return $this->minAmountPerType[$paymentMethod];
		}

		return $this->minAmount;
	}

}
