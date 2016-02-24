<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Validation;

use WMDE\Fundraising\Frontend\Domain\Model\PersonName;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class PersonNameValidator {
	use CanValidateField;

	public function validate( PersonName $name ): ValidationResult {
		if ( $name->getPersonType() === PersonName::PERSON_PRIVATE ) {
			return $this->validatePrivatePerson( $name );
		}

		return $this->validateCompanyPerson( $name );
	}

	private function validatePrivatePerson( PersonName $instance ): ValidationResult {
		$validator = new RequiredFieldValidator();

		return new ValidationResult( ...array_filter( [
			$this->getFieldViolation( $validator->validate( $instance->getSalutation() ), 'anrede' ),
			$this->getFieldViolation( $validator->validate( $instance->getFirstName() ), 'vorname' ),
			$this->getFieldViolation( $validator->validate( $instance->getLastName() ), 'nachname' )
		] ) );
	}

	private function validateCompanyPerson( PersonName $instance ): ValidationResult {
		return new ValidationResult( ...array_filter( [
			$this->getFieldViolation( ( new RequiredFieldValidator() )->validate( $instance->getCompanyName() ), 'firma' )
		] ) );
	}

}
