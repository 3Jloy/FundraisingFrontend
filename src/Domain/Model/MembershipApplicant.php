<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Domain\Model;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MembershipApplicant {

	private $personName;
	private $physicalAddress;
	private $email;
	private $dateOfBirth;

	public function __construct( PersonName $name, PhysicalAddress $address, EmailAddress $email, \DateTime $dateOfBirth ) {
		$this->personName = $name;
		$this->physicalAddress = $address;
		$this->email = $email;
		$this->dateOfBirth = $dateOfBirth;
	}

	// TODO: $applicant->getPersonName->getFirstName() is odd compared to // TODO: $applicant->getFirstName()
	public function getPersonName(): PersonName {
		return $this->personName;
	}

	public function getPhysicalAddress(): PhysicalAddress {
		return $this->physicalAddress;
	}

	public function getEmailAddress(): EmailAddress {
		return $this->email;
	}

	public function getDateOfBirth(): \DateTime {
		return $this->dateOfBirth;
	}

	public function changeEmailAddress( EmailAddress $email ) {
		$this->email = $email;
	}

	// TODO: phone number
	// Create a PhoneNumber class?
	// Use something like https://github.com/giggsey/libphonenumber-for-php?

}
