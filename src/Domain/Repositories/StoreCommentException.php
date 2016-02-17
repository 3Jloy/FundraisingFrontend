<?php

namespace WMDE\Fundraising\Frontend\Domain\Repositories;

/**
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class StoreCommentException extends \RuntimeException {

	public function __construct( \Exception $previous = null ) {
		parent::__construct( 'Could not store comment', 0, $previous );
	}

}
