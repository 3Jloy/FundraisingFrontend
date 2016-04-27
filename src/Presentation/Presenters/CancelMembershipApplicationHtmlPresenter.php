<?php

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\Presentation\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\CancelMembershipApplication\CancellationResponse;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class CancelMembershipApplicationHtmlPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( CancellationResponse $response ): string {
		return $this->template->render( [
			'membershipId' => $response->getMembershipApplicationId(),
			'cancellationSuccessful' => $response->cancellationWasSuccessful()
		] );
	}

}
