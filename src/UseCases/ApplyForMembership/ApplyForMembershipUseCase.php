<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\ApplyForMembership;

use WMDE\Fundraising\Frontend\Domain\Model\MembershipApplication;
use WMDE\Fundraising\Frontend\Domain\Repositories\MembershipApplicationRepository;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipAppAuthUpdater;
use WMDE\Fundraising\Frontend\Infrastructure\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Infrastructure\TokenGenerator;
use WMDE\Fundraising\Frontend\Validation\MembershipApplicationValidator;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApplyForMembershipUseCase {

	private $repository;
	private $authUpdater;
	private $mailer;
	private $tokenGenerator;
	private $validator;

	public function __construct( MembershipApplicationRepository $repository,
		MembershipAppAuthUpdater $authUpdater, TemplateBasedMailer $mailer,
		TokenGenerator $tokenGenerator, MembershipApplicationValidator $validator ) {

		$this->repository = $repository;
		$this->authUpdater = $authUpdater;
		$this->mailer = $mailer;
		$this->tokenGenerator = $tokenGenerator;
		$this->validator = $validator;
	}

	public function applyForMembership( ApplyForMembershipRequest $request ): ApplyForMembershipResponse {
		$application = $this->newApplicationFromRequest( $request );

		if ( !$this->validator->validate( $application )->isSuccessful() ) {
			// TODO: return failures (note that we have infrastructure failures that are not ConstraintViolations)
			return ApplyForMembershipResponse::newFailureResponse();
		}

		// TODO: handle exceptions
		$this->repository->storeApplication( $application );

		$accessToken = $this->tokenGenerator->generateToken();
		$updateToken = $this->tokenGenerator->generateToken();

		// TODO: handle exceptions
		$this->authUpdater->allowAccessViaToken( $application->getId(), $accessToken );
		$this->authUpdater->allowModificationViaToken( $application->getId(), $updateToken );

		// TODO: handle exceptions
		$this->sendConfirmationEmail( $application );

		return ApplyForMembershipResponse::newSuccessResponse( $accessToken, $updateToken );
	}

	private function newApplicationFromRequest( ApplyForMembershipRequest $request ): MembershipApplication {
		return ( new MembershipApplicationBuilder() )->newApplicationFromRequest( $request );
	}

	private function sendConfirmationEmail( MembershipApplication $application ) {
		$this->mailer->sendMail(
			$application->getApplicant()->getEmailAddress(),
			[] // TODO
		);
	}

}
