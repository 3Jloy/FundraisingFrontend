<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Integration\Validation;

use WMDE\Fundraising\Frontend\UseCases\GetInTouch\GetInTouchRequest;
use WMDE\Fundraising\Frontend\Validation\MailValidator;
use WMDE\Fundraising\Frontend\Validation\GetInTouchValidator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class GetInTouchValidatorTest extends ValidatorTestCase {

	public function testNameFieldsAreOptional() {
		$mailValidator = new MailValidator( MailValidator::TEST_WITHOUT_MX );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@meyer.net',
			'Hello there!',
			'I just wanted to say "Hi".'
		);
		$this->assertTrue( $validator->validate( $request ) );
	}

	public function testEmailAddressIsValidated() {
		$mailValidator = new MailValidator( MailValidator::TEST_WITHOUT_MX );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@meyer',
			'Hello there!',
			'I just wanted to say "Hi".'
		);
		$this->assertFalse( $validator->validate( $request ) );
		$this->assertConstraintWasViolated( $validator->getConstraintViolations(), 'email' );
	}

	public function testSubjectIsValidated() {
		$mailValidator = new MailValidator( MailValidator::TEST_WITHOUT_MX );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@net.meyer',
			'',
			'I just wanted to say "Hi".'
		);
		$this->assertFalse( $validator->validate( $request ) );
		$this->assertConstraintWasViolated( $validator->getConstraintViolations(), 'Betreff' );
	}

	public function testMessageBodyIsValidated() {
		$mailValidator = new MailValidator( MailValidator::TEST_WITHOUT_MX );
		$validator = new GetInTouchValidator( $mailValidator );
		$request = new GetInTouchRequest(
			'',
			'',
			'kh@net.meyer',
			'Hello there!',
			''
		);
		$this->assertFalse( $validator->validate( $request ) );
		$this->assertConstraintWasViolated( $validator->getConstraintViolations(), 'kommentar' );
	}

}
