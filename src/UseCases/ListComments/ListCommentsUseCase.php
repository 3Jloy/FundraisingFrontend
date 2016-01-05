<?php

namespace WMDE\Fundraising\Frontend\UseCases\ListComments;

use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\Domain\CommentRepository;

class ListCommentsUseCase {

	private $commentRepository;

	public function __construct( CommentRepository $commentRepository ) {
		$this->commentRepository = $commentRepository;
	}

	public function listComments( CommentListingRequest $listingRequest ) {
		return new CommentList( ...$this->getListItems( $listingRequest ) );
	}

	private function getListItems( CommentListingRequest $listingRequest ): array {
		return array_map(
			function( Comment $comment ) {
				return new CommentListItem(
					$comment->getAuthorName(),
					$comment->getCommentText(),
					$comment->getDonationAmount(),
					$comment->getPostingTime()
				);
			},
			$this->commentRepository->getComments( $listingRequest->getLimit() )
		);
	}

}
