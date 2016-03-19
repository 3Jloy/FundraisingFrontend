<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface DonationAuthorizer {

	/**
	 * Should return false on infrastructure failure.
	 */
	public function canModifyDonation( int $donationId ): bool;

	/**
	 * Should return false on infrastructure failure.
	 */
	public function canAccessDonation( int $donationId ): bool;

}
