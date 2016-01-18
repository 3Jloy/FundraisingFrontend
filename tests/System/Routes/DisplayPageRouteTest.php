<?php

namespace WMDE\Fundraising\Frontend\Tests\System\Routes;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\Request;
use Mediawiki\Api\UsageException;
use WMDE\Fundraising\Frontend\Tests\Fixtures\ApiPostRequestHandler;
use WMDE\Fundraising\Frontend\Tests\System\SystemTestCase;

/**
 * @covers WMDE\Fundraising\Frontend\Presenters\DisplayPagePresenter
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DisplayPageRouteTest extends SystemTestCase {

	public function setUp() {
		parent::setUp();

		$api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();

		$api->expects( $this->any() )
			->method( 'postRequest' )
			->willReturnCallback( function( Request $request ) {
				throw new UsageException( 'Page not found: ' . $request->getParams()['page'] );
			} );

		$this->getFactory()->setMediaWikiApi( $api );
	}

	public function testWhenPageDoesNotExist_missingResponseIsReturned() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'Could not load main content!',
			$client->getResponse()->getContent()
		);
	}

	public function testFooterAndHeaderGetEmbedded() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/kittens' );

		$this->assertContains(
			'Could not load header!',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'Could not load footer!',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenPageDoesNotExist_noUnescapedPageNameIsShown() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/<script>alert("kittens");' );

		$this->assertNotContains(
			'<script>alert("kittens")',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenRequestedPageExists_itGetsEmbedded() {
		$api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();

		$api->expects( $this->atLeastOnce() )
			->method( 'login' )
			->with( new ApiUser(
				$this->getConfig()['cms-wiki-user'],
				$this->getConfig()['cms-wiki-password']
			) );

		$api->expects( $this->any() )
			->method( 'postRequest' )
			->willReturnCallback( new ApiPostRequestHandler( $this->testEnvironment ) );

		$this->getFactory()->setMediaWikiApi( $api );

		$client = $this->createClient();
		$client->request( 'GET', '/page/unicorns' );

		$this->assertContains(
			'<p>Pink fluffy unicorns dancing on rainbows</p>',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'<p>I\'m a header</p>',
			$client->getResponse()->getContent()
		);

		$this->assertContains(
			'<p>I\'m a footer</p>',
			$client->getResponse()->getContent()
		);
	}

	public function testWhenPageNameContainsSlash_404isReturned() {
		$client = $this->createClient();
		$client->request( 'GET', '/page/unicorns/of-doom' );

		$this->assert404( $client->getResponse(), 'No route found for "GET /page/unicorns/of-doom"' );
	}

	public function testWhenNoSubFooter_subFooterDivIsNotShown() {
		// TODO: setup
		$client = $this->createClient();
		$client->request( 'GET', '/page/unicorns' );

		$this->assertNotContains(
			'<div id="subfooter">',
			$client->getResponse()->getContent()
		);
	}

}
