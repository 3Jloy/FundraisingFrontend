<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Presenters;

use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\TwigTemplate;
use WMDE\Fundraising\Frontend\UseCases\ListComments\CommentList;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class CommentListHtmlPresenter {

	private $template;

	public function __construct( TwigTemplate $template ) {
		$this->template = $template;
	}

	public function present( CommentList $commentList ): string {
		return $this->template->render( [
			'comments' => array_map(
				function( Comment $comment ) {
					return [
						'amount' => $comment->getDonationAmount(),
						'author' => $comment->getAuthorName(),
						'text' => $comment->getCommentText(),
						'publicationDate' => $comment->getPostingTime()->format( 'r' ),
					];
				},
				$commentList->toArray()
			),
		] );
	}

}