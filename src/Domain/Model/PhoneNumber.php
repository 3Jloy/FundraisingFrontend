<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PhoneNumber {

	private $phoneNumber;

	public function __construct( string $phoneNumber ) {
		if ( $phoneNumber === '' ) {
			throw new \InvalidArgumentException( '$phoneNumber cannot be empty' );
		}

		$this->phoneNumber = $phoneNumber;
	}

	public function __toString(): string {
		return $this->phoneNumber;
	}

}