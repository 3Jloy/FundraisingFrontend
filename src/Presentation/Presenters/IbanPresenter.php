<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Presentation\Presenters;

use WMDE\Fundraising\Frontend\PaymentContext\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\PaymentContext\ResponseModel\IbanResponse;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class IbanPresenter {

	public function present( IbanResponse $iban ) {
		if ( $iban->isSuccessful() ) {
			return $this->newSuccessResponse( $iban->getBankData() );
		}

		return $this->newErrorResponse();
	}

	private function newSuccessResponse( BankData $bankData ) {
		return array_filter( [
			'status' => 'OK',
			'bic' => $bankData->getBic(),
			'iban' => $bankData->getIban()->toString(),
			'account' => $bankData->getAccount(),
			'bankCode' => $bankData->getBankCode(),
			'bankName' => $bankData->getBankName(),
		] );
	}

	private function newErrorResponse() {
		return [ 'status' => 'ERR' ];
	}

}