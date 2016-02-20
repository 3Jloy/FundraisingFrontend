<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Domain\Model;

use WMDE\Fundraising\Frontend\FreezableValueObject;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TrackingInfo {
	use FreezableValueObject;

	private $tracking;
	private $source;
	private $totalImpressionCount;
	private $singleBannerImpressionCount;
	private $color;
	private $skin;
	private $layout;

	public function getTracking(): string {
		return $this->tracking;
	}

	public function setTracking( string $tracking ) {
		$this->assertIsWritable();
		$this->tracking = $tracking;
	}

	public function getSource(): string {
		return $this->source;
	}

	public function setSource( string $source ) {
		$this->source = $source;
	}

	public function getTotalImpressionCount(): int {
		return $this->totalImpressionCount;
	}

	public function setTotalImpressionCount( int $totalImpressionCount ) {
		$this->totalImpressionCount = $totalImpressionCount;
	}

	public function getSingleBannerImpressionCount(): int {
		return $this->singleBannerImpressionCount;
	}

	public function setSingleBannerImpressionCount( int $singleBannerImpressionCount ) {
		$this->singleBannerImpressionCount = $singleBannerImpressionCount;
	}

	public function getColor(): string {
		return $this->color;
	}

	public function setColor( string $color ) {
		$this->color = $color;
	}

	public function getSkin(): string {
		return $this->skin;
	}

	public function setSkin( string $skin ) {
		$this->skin = $skin;
	}

	public function getLayout(): string {
		return $this->layout;
	}

	public function setLayout( string $layout ) {
		$this->layout = $layout;
	}

}
