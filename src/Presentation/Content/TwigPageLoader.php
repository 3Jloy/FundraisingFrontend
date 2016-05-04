<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Content;

use Twig_Error_Loader;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigPageLoader implements \Twig_LoaderInterface {

	private $contentProvider;
	private $pageCache = [];
	private $errorCache = [];
	private $rawPagesList = [];

	public function __construct( WikiContentProvider $contentProvider, array $rawPagesList = [] ) {
		$this->contentProvider = $contentProvider;
		$this->rawPagesList = $rawPagesList;
	}

	public function getSource( $name ): string {
		return $this->retrievePage( $name );
	}

	public function getCacheKey( $name ): string {
		// retrieve page to generate loader exception if page does not exist
		$this->retrievePage( $name );
		return $name;
	}

	// @codingStandardsIgnoreStart
	public function isFresh( $name, $time ): bool {
		// @codingStandardsIgnoreEnd

		// retrieve page to generate loader exception if page does not exist
		$this->retrievePage( $name );
		return true;
	}

	private function retrievePage( string $pageName ): string {
		$fetchMode = in_array( $pageName, $this->rawPagesList ) ? 'raw' : 'render';
		$title = preg_replace( '/\\.twig$/', '', $pageName );
		if ( isset( $this->pageCache[$title] ) ) {
			return $this->pageCache[$title];
		}
		if ( isset( $this->errorCache[$title] ) ) {
			throw new Twig_Error_Loader( "Wiki page $title not found." );
		}
		$content = $this->contentProvider->getContent( $title, $fetchMode );
		if ( $content !== '' ) {
			$this->pageCache[$title] = $content;
			return $content;
		}
		$this->errorCache[$title] = true;
		throw new Twig_Error_Loader( "Wiki page $title not found." );
	}

}