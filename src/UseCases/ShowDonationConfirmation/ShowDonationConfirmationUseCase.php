<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ShowDonationConfirmation;

use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ShowDonationConfirmationUseCase {

	private $authorizer;
	private $donationRepository;

	public function __construct( DonationAuthorizer $authorizer, DonationRepository $donationRepository ) {
		$this->authorizer = $authorizer;
		$this->donationRepository = $donationRepository;
	}

	public function showConfirmation( ShowDonationConfirmationRequest $request ): ShowDonationConfirmationResponse {
		if ( $this->authorizer->canAccessDonation( $request->getDonationId() ) ) {
			$donation = $this->getDonationById( $request->getDonationId() );

			if ( $donation !== null ) {
				return ShowDonationConfirmationResponse::newValidResponse( $donation );
			}
		}

		return ShowDonationConfirmationResponse::newNotAllowedResponse();
	}

	private function getDonationById( int $donationId ) {
		try {
			return $this->donationRepository->getDonationById( $donationId );
		}
		catch ( GetDonationException $ex ) {
			return null;
		}
	}

}