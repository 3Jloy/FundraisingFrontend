<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Data;

use WMDE\Fundraising\Frontend\Domain\Model\BankTransferPayment;
use WMDE\Fundraising\Frontend\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentMethod;
use WMDE\Fundraising\Frontend\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentWithoutAssociatedData;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Domain\Model\TrackingInfo;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidDonation {

	const DONOR_FIRST_NAME = 'Jeroen';
	const DONOR_LAST_NAME = 'De Dauw';
	const DONOR_SALUTATION = 'nyan';
	const DONOR_TITLE = 'nyan';

	const DONOR_CITY = 'Berlin';
	const DONOR_POSTAL_CODE = '1234';
	const DONOR_COUNTRY_CODE = 'DE';
	const DONOR_STREET_ADDRESS = 'Nyan Street';

	const DONOR_EMAIL_ADDRESS = 'foo@bar.baz';

	const DONATION_AMOUNT = 13.37; // Keep fractional to detect floating point issues
	const PAYMENT_INTERVAL_IN_MONTHS = 3;

	const PAYMENT_BANK_ACCOUNT = '0648489890';
	const PAYMENT_BANK_CODE = '50010517';
	const PAYMENT_BANK_NAME = 'ING-DiBa';
	const PAYMENT_BIC = 'INGDDEFFXXX';
	const PAYMENT_IBAN = 'DE12500105170648489890';

	const PAYMENT_BANK_TRANSFER_CODE = 'pink fluffy unicorns';

	const OPTS_INTO_NEWSLETTER = Donation::OPTS_INTO_NEWSLETTER;

	public static function newBankTransferDonation(): Donation {
		return ( new self() )->createDonation(
			new BankTransferPayment( self::PAYMENT_BANK_TRANSFER_CODE ),
			Donation::STATUS_NEW
		);
	}

	public static function newDirectDebitDonation(): Donation {
		return ( new self() )->createDonation(
			new DirectDebitPayment( self::newBankData() ),
			Donation::STATUS_NEW
		);
	}

	public static function newBookedPayPalDonation(): Donation {
		return ( new self() )->createDonation(
			new PaymentWithoutAssociatedData( PaymentType::PAYPAL ),
			Donation::STATUS_EXTERNAL_BOOKED
		);
	}

	public static function newIncompletePayPalDonation(): Donation {
		return ( new self() )->createDonation(
			new PaymentWithoutAssociatedData( PaymentType::PAYPAL ),
			Donation::STATUS_EXTERNAL_INCOMPLETE
		);
	}

	private function createDonation( PaymentMethod $paymentMethod, string $status ): Donation {
		return new Donation(
			null,
			$status,
			$this->newDonor(),
			new DonationPayment(
				Euro::newFromFloat( self::DONATION_AMOUNT ),
				self::PAYMENT_INTERVAL_IN_MONTHS,
				$paymentMethod
			),
			self::OPTS_INTO_NEWSLETTER,
			$this->newTrackingInfo()
		);
	}

	public static function newDonor(): Donor {
		$self = new self();

		return new Donor(
			$self->newPersonName(),
			$self->newAddress(),
			'foo@bar.baz'
		);
	}

	private function newPersonName(): PersonName {
		$personName = PersonName::newPrivatePersonName();

		$personName->setFirstName( self::DONOR_FIRST_NAME );
		$personName->setLastName( self::DONOR_LAST_NAME );
		$personName->setSalutation( self::DONOR_SALUTATION );
		$personName->setTitle( self::DONOR_TITLE );

		return $personName->freeze()->assertNoNullFields();
	}

	private function newAddress(): PhysicalAddress {
		$address = new PhysicalAddress();

		$address->setCity( self::DONOR_CITY );
		$address->setCountryCode( self::DONOR_COUNTRY_CODE );
		$address->setPostalCode( self::DONOR_POSTAL_CODE );
		$address->setStreetAddress( self::DONOR_STREET_ADDRESS );

		return $address->freeze()->assertNoNullFields();
	}

	public static function newTrackingInfo(): TrackingInfo {
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

	public static function newBankData(): BankData {
		$bankData = new BankData();

		$bankData->setAccount( self::PAYMENT_BANK_ACCOUNT );
		$bankData->setBankCode( self::PAYMENT_BANK_CODE );
		$bankData->setBankName( self::PAYMENT_BANK_NAME );
		$bankData->setBic( self::PAYMENT_BIC );
		$bankData->setIban( new Iban( self::PAYMENT_IBAN ) );

		return $bankData->freeze()->assertNoNullFields();
	}

}
