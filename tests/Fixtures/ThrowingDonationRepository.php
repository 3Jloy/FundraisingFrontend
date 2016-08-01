<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\StoreDonationException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ThrowingDonationRepository implements DonationRepository {

	private $throwOnStoreDonation;
	private $onGetDonationById;

	public function throwOnStoreDonation() {
		$this->throwOnStoreDonation = true;
	}

	public function throwOnGetDonationById() {
		$this->onGetDonationById = true;
	}

	/**
	 * When storing a not yet persisted Donation, a new id will be generated and assigned to it.
	 * This means the id of new donations needs to be null. The id can be accessed by calling getId on
	 * the passed in Donation.
	 *
	 * @param \WMDE\Fundraising\Frontend\DonatingContext\Domain\Model\Donation $donation
	 *
	 * @throws \WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\StoreDonationException
	 */
	public function storeDonation( Donation $donation ) {
		if ( $this->throwOnStoreDonation ) {
			throw new StoreDonationException();
		}
	}

	/**
	 * @param int $id
	 *
	 * @return Donation|null
	 * @throws \WMDE\Fundraising\Frontend\DonatingContext\Domain\Repositories\GetDonationException
	 */
	public function getDonationById( int $id ) {
		if ( $this->onGetDonationById ) {
			throw new GetDonationException();
		}
	}

}