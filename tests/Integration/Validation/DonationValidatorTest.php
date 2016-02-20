<?php

namespace WMDE\Fundraising\Tests\Unit;

use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\Domain\Model\PersonalInfo;
use WMDE\Fundraising\Frontend\Domain\NullDomainNameValidator;
use WMDE\Fundraising\Frontend\Domain\Model\PersonName;
use WMDE\Fundraising\Frontend\Domain\Model\PhysicalAddress;
use WMDE\Fundraising\Frontend\Tests\Unit\Validation\ValidatorTestCase;
use WMDE\Fundraising\Frontend\Validation\AllowedValuesValidator;
use WMDE\Fundraising\Frontend\Validation\AmountPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\AmountValidator;
use WMDE\Fundraising\Frontend\Validation\BankDataValidator;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;
use WMDE\Fundraising\Frontend\Validation\IbanValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\PersonNameValidator;
use WMDE\Fundraising\Frontend\Validation\PhysicalAddressValidator;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;
use WMDE\Fundraising\Frontend\Validation\ValidationResult;

/**
 * @covers WMDE\Fundraising\Frontend\Validation\DonationValidator
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationValidatorTest extends ValidatorTestCase {

	/** @var DonationValidator */
	private $donationValidator;

	public function setUp() {
		$this->donationValidator = $this->newDonationValidator();
	}

	public function testGivenValidDonation_validationIsSuccessful() {
		$personalInfo = new PersonalInfo();
		$personalInfo->setPersonName( $this->newCompanyName() );
		$personalInfo->setPhysicalAddress( $this->newPhysicalAddress() );
		$personalInfo->setEmailAddress( 'hank.scorpio@globex.com' );
		$personalInfo->freeze()->assertNoNullFields();
		$donation = $this->newDonation( $personalInfo );

		$this->assertEmpty( $this->donationValidator->validate( $donation )->getViolations() );
	}

	public function testNoPersonalInfoGiven_validatorReturnsTrue() {
		$donation = new Donation();
		$donation->setAmount( 1 );
		$donation->setPaymentType( PaymentType::BANK_TRANSFER );
		$this->assertTrue( $this->donationValidator->validate( $donation )->isSuccessful() );
	}

	public function testTooHighAmountGiven_needsModerationReturnsTrue() {
		$donation = new Donation();
		$donation->setAmount( 350 );
		$donation->setInterval( 12 );
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );
		$this->assertTrue( $this->donationValidator->needsModeration( $donation ) );
	}

	public function testPartlyPersonalInfoGiven_validatorReturnsFalse() {
		$personalInfo = new PersonalInfo();
		$personalInfo->setPersonName( PersonName::newCompanyName() );
		$personalInfo->setPhysicalAddress( new PhysicalAddress() );
		$personalInfo->setEmailAddress( 'hank.scorpio@globex.com' );
		$personalInfo->freeze()->assertNoNullFields();

		$donation = new Donation();
		$donation->setAmount( 1 );
		$donation->setPaymentType( PaymentType::BANK_TRANSFER );
		$donation->setPersonalInfo( $personalInfo );
		$donation->freeze();

		$this->assertFalse( $this->donationValidator->validate( $donation )->isSuccessful() );

		$this->assertConstraintWasViolated(
			$this->donationValidator->validate( $donation ),
			'firma'
		);
	}

	public function testGivenBadWords_needsModerationReturnsTrue() {
		$textPolicyValidator = $this->getMock( TextPolicyValidator::class );
		$textPolicyValidator->method( 'hasHarmlessContent' )
			->willReturn( false );

		$donationValidator = new DonationValidator(
			new AmountValidator( 1 ),
			new AmountPolicyValidator( 1000, 200, 300 ),
			$textPolicyValidator,
			new PersonNameValidator(),
			new PhysicalAddressValidator(),
			new AllowedValuesValidator( [ PaymentType::DIRECT_DEBIT ] ),
			$this->newBankDataValidator(),
			new MailValidator( new NullDomainNameValidator() )
		);

		$personalInfo = new PersonalInfo();
		$personalInfo->setPersonName( $this->newCompanyName() );
		$personalInfo->setPhysicalAddress( $this->newPhysicalAddress() );
		$personalInfo->setEmailAddress( 'hank.scorpio@globex.com' );
		$personalInfo->freeze();

		$donation = $this->newDonation( $personalInfo );
		$this->assertTrue( $donationValidator->needsModeration( $donation ) );
	}

	public function testNoPaymentTypeGiven_validatorReturnsFalse() {
		$donation = new Donation();
		$donation->setAmount( 1 );
		$donation->setPaymentType( '' );
		$donation->freeze();

		$this->assertFalse( $this->donationValidator->validate( $donation )->isSuccessful() );

		$this->assertConstraintWasViolated(
			$this->donationValidator->validate( $donation ),
			'zahlweise'
		);
	}

	public function testUnsupportedPaymentTypeGiven_validatorReturnsFalse() {
		$donation = new Donation();
		$donation->setAmount( 1 );
		$donation->setPaymentType( PaymentType::PAYPAL );
		$donation->freeze();

		$this->assertFalse( $this->donationValidator->validate( $donation )->isSuccessful() );

		$this->assertConstraintWasViolated(
			$this->donationValidator->validate( $donation ),
			'zahlweise'
		);
	}

	public function testDirectDebitMissingBankData_validatorReturnsFalse() {
		$donation = new Donation();
		$donation->setAmount( 1 );
		$donation->setPaymentType( PaymentType::DIRECT_DEBIT );
		$donation->setBankData( $this->newValidBankData() );
		$donation->freeze();

		$validationResult = $this->donationValidator->validate( $donation );
		$this->assertFalse( $validationResult->isSuccessful() );
		$this->assertConstraintWasViolated( $validationResult, 'iban' );
		$this->assertConstraintWasViolated( $validationResult, 'bic' );
		$this->assertConstraintWasViolated( $validationResult, 'bankname' );
	}

	private function newCompanyName(): PersonName {
		$name = PersonName::newCompanyName();
		$name->setCompanyName( 'Globex Corp.' );
		return $name;
	}

	private function newPhysicalAddress(): PhysicalAddress {
		$address = new PhysicalAddress();
		$address->setStreetAddress( 'PO box 1234' );
		$address->setPostalCode( '90701' );
		$address->setCity( 'Cypress Creek' );
		$address->setCountryCode( 'US' );
		$address->freeze()->assertNoNullFields();
		return $address;
	}

	private function newDonationValidator(): DonationValidator {
		return new DonationValidator(
			new AmountValidator( 1 ),
			new AmountPolicyValidator( 1000, 200, 300 ),
			new TextPolicyValidator(),
			new PersonNameValidator(),
			new PhysicalAddressValidator(),
			new AllowedValuesValidator( [ PaymentType::DIRECT_DEBIT, PaymentType::BANK_TRANSFER ] ),
			$this->newBankDataValidator(),
			new MailValidator( new NullDomainNameValidator() )
		);
	}

	private function newDonation( PersonalInfo $personalInfo ): Donation {
		$donation = new Donation();
		$donation->setAmount( 1 );
		$donation->setPersonalInfo( $personalInfo );
		$donation->setPaymentType( PaymentType::BANK_TRANSFER );
		$donation->freeze();

		return $donation;
	}

	private function newValidBankData() {
		$bankData = new BankData();
		$bankData->setIban( new Iban( '' ) );
		$bankData->setBic( '' );
		$bankData->setAccount( '' );
		$bankData->setBankCode( '' );
		$bankData->setBankName( '' );
		return $bankData;
	}

	private function newBankDataValidator() {
		$ibanValidatorMock = $this->getMockBuilder( IbanValidator::class )->disableOriginalConstructor()->getMock();
		$ibanValidatorMock->method( 'validate' )
			->willReturn( new ValidationResult() );

		return new BankDataValidator( $ibanValidatorMock );
	}

}
