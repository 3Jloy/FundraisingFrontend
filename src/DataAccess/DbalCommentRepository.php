<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityRepository;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\Domain\CommentRepository;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DbalCommentRepository implements CommentRepository {

	private $entityRepository;

	public function __construct( EntityRepository $entityRepository ) {
		$this->entityRepository = $entityRepository;
	}

	/**
	 * @see CommentRepository::getPublicComments
	 *
	 * @param int $limit
	 *
	 * @return Comment[]
	 */
	public function getPublicComments( int $limit ): array {
		return array_map(
			function( Donation $donation ) {
				return Comment::newInstance()
					->setAuthorName( $donation->getName() )
					->setCommentText( $donation->getComment() )
					->setDonationAmount( (float)$donation->getAmount() )
					->setPostingTime( $donation->getDtNew() )
					->setDonationId( $donation->getId() )
					->freeze()
					->assertNoNullFields();
			},
			$this->getDonation( $limit )
		);
	}

	private function getDonation( int $limit ): array {
		return $this->entityRepository->findBy(
			[
				'isPublic' => true,
				'dtDel' => null
			],
			[
				'dtNew' => 'DESC'
			],
			$limit
		);
	}

}
