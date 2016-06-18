<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\AddComment;

use WMDE\Fundraising\Frontend\Domain\Model\DonationComment;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Repositories\GetDonationException;
use WMDE\Fundraising\Frontend\Domain\Repositories\StoreDonationException;
use WMDE\Fundraising\Frontend\Infrastructure\DonationAuthorizer;
use WMDE\Fundraising\Frontend\Validation\TextPolicyValidator;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class AddCommentUseCase {

	private $donationRepository;
	private $authorizationService;
	private $textPolicyValidator;

	public function __construct( DonationRepository $repository, DonationAuthorizer $authorizationService,
		TextPolicyValidator $textPolicyValidator ) {

		$this->donationRepository = $repository;
		$this->authorizationService = $authorizationService;
		$this->textPolicyValidator = $textPolicyValidator;
	}

	public function addComment( AddCommentRequest $addCommentRequest ): AddCommentResponse {
		if ( !$this->requestIsAllowed( $addCommentRequest ) ) {
			return AddCommentResponse::newFailureResponse( 'comment_failure_access_denied' );
		}

		try {
			$donation = $this->donationRepository->getDonationById( $addCommentRequest->getDonationId() );
		}
		catch ( GetDonationException $ex ) {
			return AddCommentResponse::newFailureResponse( 'comment_failure_donation_error' );
		}

		if ( $donation === null ) {
			return AddCommentResponse::newFailureResponse( 'comment_failure_donation_not_found' );
		}

		if ( $donation->getComment() !== null ) {
			return AddCommentResponse::newFailureResponse( 'comment_failure_donation_has_comment' );
		}

		$successMessage = 'comment_success_ok';
		if ( $donation->needsModeration() ) {
			$successMessage = 'comment_success_needs_moderation';
		}

		$donation->addComment( $this->newCommentFromRequest( $addCommentRequest ) );

		if ( !$this->commentTextPassesValidation( $addCommentRequest->getCommentText() ) ) {
			$donation->markForModeration();
			$successMessage = 'comment_success_needs_moderation';
		}

		try {
			$this->donationRepository->storeDonation( $donation );
		}
		catch ( StoreDonationException $ex ) {
			return AddCommentResponse::newFailureResponse( 'comment_failure_save_error' );
		}

		return AddCommentResponse::newSuccessResponse( $successMessage );
	}

	private function requestIsAllowed( AddCommentRequest $addCommentRequest ): bool {
		return $this->authorizationService->userCanModifyDonation( $addCommentRequest->getDonationId() );
	}

	private function newCommentFromRequest( AddCommentRequest $request ): DonationComment {
		return new DonationComment(
			$request->getCommentText(),
			$this->commentCanBePublic( $request ),
			$request->getAuthorDisplayName()
		);
	}

	private function commentCanBePublic( AddCommentRequest $request ): bool {
		return $request->isPublic()
			&& $this->commentTextPassesValidation( $request->getCommentText() );
	}

	private function commentTextPassesValidation( string $text ): bool {
		return $this->textPolicyValidator->textIsHarmless( $text );
	}

}