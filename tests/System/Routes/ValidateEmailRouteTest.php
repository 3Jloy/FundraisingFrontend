<?php

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\Tests\System\SystemTestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidateEmailRouteTest extends SystemTestCase {

	public function testGivenValidEmail_successResponseIsReturned() {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/validate-email',
			[ 'email' => 'jeroendedauw@gmail.com' ]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'OK' ],
			$client->getResponse()
		);
	}

	public function testGivenInvalidEmail_errorResponseIsReturned() {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/validate-email',
			[ 'email' => '~=[,,_,,]:3' ]
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'ERR' ],
			$client->getResponse()
		);
	}

	public function testGivenNoEmail_errorResponseIsReturned() {
		$client = $this->createClient();

		$client->request(
			'GET',
			'/validate-email'
		);

		$this->assertJsonSuccessResponse(
			[ 'status' => 'ERR' ],
			$client->getResponse()
		);
	}

}
