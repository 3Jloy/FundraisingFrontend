<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain\Model;

use RuntimeException;
use WMDE\Fundraising\Frontend\Domain\Model\DirectDebitPayment;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Model\DonationComment;
use WMDE\Fundraising\Frontend\Domain\Model\DonationPayment;
use WMDE\Fundraising\Frontend\Domain\Model\Euro;
use WMDE\Fundraising\Frontend\Domain\Model\PayPalData;
use WMDE\Fundraising\Frontend\Tests\Data\ValidDonation;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\Model\Donation
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DonationTest extends \PHPUnit_Framework_TestCase {

	public function testGivenNonDirectDebitDonation_cancellationFails() {
		$donation = ValidDonation::newBankTransferDonation();

		$this->expectException( RuntimeException::class );
		$donation->cancel();
	}

	public function testGivenDirectDebitDonation_cancellationSucceeds() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->cancel();
		$this->assertSame( Donation::STATUS_CANCELLED, $donation->getStatus() );
	}

	/**
	 * @dataProvider nonCancellableStatusProvider
	 */
	public function testGivenNonNewStatus_cancellationFails( $nonCancellableStatus ) {
		$donation = $this->newDirectDebitDonationWithStatus( $nonCancellableStatus );

		$this->expectException( RuntimeException::class );
		$donation->cancel();
	}

	private function newDirectDebitDonationWithStatus( string $status ) {
		return new Donation(
			null,
			$status,
			ValidDonation::newDonor(),
			ValidDonation::newDirectDebtPayment(),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo()
		);
	}

	public function nonCancellableStatusProvider() {
		return [
			[ Donation::STATUS_CANCELLED ],
			[ Donation::STATUS_EXTERNAL_BOOKED ],
			[ Donation::STATUS_EXTERNAL_INCOMPLETE ],
			[ Donation::STATUS_PROMISE ],
		];
	}

	public function testGivenNewStatus_cancellationSucceeds() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->cancel();
		$this->assertSame( Donation::STATUS_CANCELLED, $donation->getStatus() );
	}

	public function testModerationStatusCanBeQueried() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->markForModeration();
		$this->assertTrue( $donation->needsModeration() );
	}

	public function testGivenModerationStatus_cancellationSucceeds() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->markForModeration();
		$donation->cancel();
		$this->assertSame( Donation::STATUS_CANCELLED, $donation->getStatus() );
	}

	public function testIdIsNullWhenNotAssigned() {
		$this->assertNull( ValidDonation::newDirectDebitDonation()->getId() );
	}

	public function testCanAssignIdToNewDonation() {
		$donation = ValidDonation::newDirectDebitDonation();

		$donation->assignId( 42 );
		$this->assertSame( 42, $donation->getId() );
	}

	public function testCannotAssignIdToDonationWithIdentity() {
		$donation = ValidDonation::newDirectDebitDonation();
		$donation->assignId( 42 );

		$this->expectException( RuntimeException::class );
		$donation->assignId( 43 );
	}

	public function testGivenNonExternalPaymentType_confirmBookedThrowsException() {
		$donation = ValidDonation::newDirectDebitDonation();

		$this->setExpectedExceptionRegExp( RuntimeException::class, '/Only external payments/' );
		$donation->confirmBooked();
	}

	public function testAddingPayPalDataToNoPayPalDonationCausesException() {
		$donation = ValidDonation::newDirectDebitDonation();

		$this->expectException( RuntimeException::class );
		$donation->addPayPalData( new PayPalData() );
	}

	/**
	 * @dataProvider statusesThatDoNotAllowForBookingProvider
	 */
	public function testGivenStatusThatDoesNotAllowForBooking_confirmBookedThrowsException( Donation $donation ) {
		$this->expectException( RuntimeException::class );
		$donation->confirmBooked();
	}

	public function statusesThatDoNotAllowForBookingProvider() {
		return [
			[ ValidDonation::newBookedPayPalDonation() ],
			[ ValidDonation::newBookedCreditCardDonation() ],
		];
	}

	/**
	 * @dataProvider statusesThatAllowsForBookingProvider
	 */
	public function testGivenStatusThatAllowsForBooking_confirmBookedSetsBookedStatus( Donation $donation ) {
		$donation->confirmBooked();
		$this->assertSame( Donation::STATUS_EXTERNAL_BOOKED, $donation->getStatus() );
	}

	public function statusesThatAllowsForBookingProvider() {
		return [
			[ ValidDonation::newIncompletePayPalDonation() ],
			[ ValidDonation::newIncompleteCreditCardDonation() ],
			[ $this->newInModerationPayPalDonation() ],
			[ ValidDonation::newCancelledPayPalDonation() ],
		];
	}

	private function newInModerationPayPalDonation(): Donation {
		$donation = ValidDonation::newIncompletePayPalDonation();
		$donation->markForModeration();
		return $donation;
	}

	public function testAddCommentThrowsExceptionWhenCommentAlreadySet() {
		$donation = new Donation(
			null,
			Donation::STATUS_NEW,
			ValidDonation::newDonor(),
			ValidDonation::newDirectDebtPayment(),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo(),
			ValidDonation::newComment()
		);

		$this->expectException( RuntimeException::class );
		$donation->addComment( ValidDonation::newComment() );
	}

	public function testAddCommentSetsWhenCommentNotSetYet() {
		$donation = new Donation(
			null,
			Donation::STATUS_NEW,
			ValidDonation::newDonor(),
			ValidDonation::newDirectDebtPayment(),
			Donation::OPTS_INTO_NEWSLETTER,
			ValidDonation::newTrackingInfo(),
			null
		);

		$donation->addComment( ValidDonation::newComment() );
		$this->assertEquals( ValidDonation::newComment(), $donation->getComment() );
	}

	public function testWhenNoCommentHasBeenSet_getCommentReturnsNull() {
		$this->assertNull( ValidDonation::newDirectDebitDonation()->getComment() );
	}

}