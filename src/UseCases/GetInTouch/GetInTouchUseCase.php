<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\UseCases\GetInTouch;

use WMDE\Fundraising\Frontend\Domain\Model\MailAddress;
use WMDE\Fundraising\Frontend\Messenger;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\Message;
use WMDE\Fundraising\Frontend\TemplateBasedMailer;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchUseCase {

	private $validator;
	private $messenger;
	private $templateBasedMailer;

	public function __construct( GetInTouchValidator $validator, Messenger $messenger, TemplateBasedMailer $templateMailer ) {
		$this->validator = $validator;
		$this->messenger = $messenger;
		$this->templateBasedMailer = $templateMailer;
	}

	/**
	 * @throws \RuntimeException
	 */
	public function processContactRequest( GetInTouchRequest $request ): ValidationResponse {
		$validationResult = $this->validator->validate( $request );

		if ( $validationResult->hasViolations() ) {
			return ValidationResponse::newFailureResponse( $validationResult->getViolations() );
		}

		$this->forwardContactRequest( $request );
		$this->confirmToUser( $request );

		return ValidationResponse::newSuccessResponse();
	}

	private function forwardContactRequest( GetInTouchRequest $request ) {
		$this->messenger->sendMessageToOperator(
			new Message(
				$request->getSubject(),
				$request->getMessageBody()
			),
			new MailAddress( $request->getEmailAddress() )
		);
	}

	private function confirmToUser( GetInTouchRequest $request ) {
		$this->templateBasedMailer->sendMail( new MailAddress( $request->getEmailAddress() ) );
	}

}
