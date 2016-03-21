<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\PersonalInfo;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Domain\Model\TrackingInfo;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidDonation {

	private function __construct() {
	}

	public static function newDonation( float $amount = 42 ): Donation {
		return ( new self() )->createDonation( $amount );
	}

	public static function newBankTransferDonation( string $transferCode ): Donation {
		return ( new self() )->createDonation( 42, PaymentType::BANK_TRANSFER, $transferCode );
	}

	private function createDonation( float $amount,
									 string $paymentType = PaymentType::DIRECT_DEBIT,
									 string $transferCode = '' ): Donation {
		$donation = new Donation();

		$donation->setAmount( $amount );
		$donation->setPaymentType( $paymentType );
		$donation->setBankTransferCode( $transferCode );
		$donation->setStatus( Donation::STATUS_NEW );

		$donation->setOptsIntoNewsletter( true );

		$donation->setPersonalInfo( $this->newPersonalInfo() );
		$donation->setTrackingInfo( $this->newTrackingInfo() );
		$donation->setBankData( $this->newBankData() );

		return $donation;
	}

	private function newPersonalInfo(): PersonalInfo {
		return new PersonalInfo(
			$this->newPersonName(),
			$this->newAddress(),
			'foo@bar.baz'
		);
	}

	private function newPersonName(): PersonName {
		$personName = PersonName::newPrivatePersonName();

		$personName->setFirstName( 'Jeroen' );
		$personName->setLastName( 'De Dauw' );
		$personName->setSalutation( 'nyan' );
		$personName->setTitle( 'nyan' );

		return $personName->freeze()->assertNoNullFields();
	}

	private function newAddress(): PhysicalAddress {
		$address = new PhysicalAddress();

		$address->setCity( 'Berlin' );
		$address->setCountryCode( 'DE' );
		$address->setPostalCode( '1234' );
		$address->setStreetAddress( 'Nyan Street' );

		return $address->freeze()->assertNoNullFields();
	}

	private function newTrackingInfo(): TrackingInfo {
		$trackingInfo = new TrackingInfo();

		$trackingInfo->setColor( 'blue' );
		$trackingInfo->setLayout( 'Default' );
		$trackingInfo->setSingleBannerImpressionCount( 1 );
		$trackingInfo->setSkin( 'default' );
		$trackingInfo->setSource( 'web' );
		$trackingInfo->setTotalImpressionCount( 3 );
		$trackingInfo->setTracking( 'test/gelb' );

		return $trackingInfo->freeze()->assertNoNullFields();
	}

	private function newBankData(): BankData {
		$bankData = new BankData();

		$bankData->setAccount( '0648489890' );
		$bankData->setBankCode( '50010517' );
		$bankData->setBankName( 'ING-DiBa' );
		$bankData->setBic( 'INGDDEFFXXX' );
		$bankData->setIban( new Iban( 'DE12500105170648489890' ) );

		return $bankData->freeze()->assertNoNullFields();
	}

}
