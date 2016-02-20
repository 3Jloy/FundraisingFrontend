<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\AddDonation;

use WMDE\Fundraising\Frontend\Domain\Model\BankData;
use WMDE\Fundraising\Frontend\Domain\Model\Donation;
use WMDE\Fundraising\Frontend\Domain\Repositories\DonationRepository;
use WMDE\Fundraising\Frontend\Domain\Iban;
use WMDE\Fundraising\Frontend\Domain\Model\PaymentType;
use WMDE\Fundraising\Frontend\ReferrerGeneralizer;
use WMDE\Fundraising\Frontend\ResponseModel\ValidationResponse;
use WMDE\Fundraising\Frontend\Validation\DonationValidator;

/**
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class AddDonationUseCase {

	private $donationRepository;
	private $donationValidator;
	private $referrerGeneralizer;

	public function __construct( DonationRepository $donationRepository, DonationValidator $donationValidator,
								 ReferrerGeneralizer $referrerGeneralizer ) {
		$this->donationRepository = $donationRepository;
		$this->donationValidator = $donationValidator;
		$this->referrerGeneralizer = $referrerGeneralizer;
	}

	public function addDonation( AddDonationRequest $donationRequest ) {
		$donation = new Donation();

		$donation->setAmount( $donationRequest->getAmount() );
		$donation->setInterval( $donationRequest->getInterval() );
		$donation->setPersonalInfo( $donationRequest->getPersonalInfo() );
		$donation->setOptsIntoNewsletter( $donationRequest->getOptIn() === '1' );
		$donation->setPaymentType( $donationRequest->getPaymentType() );
		$donation->setTracking( $donationRequest->getTracking() );
		$donation->setSource( $this->referrerGeneralizer->generalize( $donationRequest->getSource() ) );
		$donation->setTotalImpressionCount( $donationRequest->getTotalImpressionCount() );
		$donation->setSingleBannerImpressionCount( $donationRequest->getSingleBannerImpressionCount() );
		$donation->setColor( $donationRequest->getColor() );
		$donation->setSkin( $donationRequest->getSkin() );
		$donation->setLayout( $donationRequest->getLayout() );

		// TODO: try to complement bank data if some fields are missing
		if ( $donationRequest->getPaymentType() === PaymentType::DIRECT_DEBIT ) {
			$donation->setBankData( $this->newBankDataFromRequest( $donationRequest ) );
		}

		$validationResult = $this->donationValidator->validate( $donation );

		if ( $validationResult->hasViolations() ) {
			return ValidationResponse::newFailureResponse( $validationResult->getViolations() );
		}

		$this->donationRepository->storeDonation( $donation );

		// TODO: send mails

		return ValidationResponse::newSuccessResponse();
	}

	private function newBankDataFromRequest( AddDonationRequest $request ): BankData {
		$bankData = new BankData();
		$bankData->setIban( new Iban( $request->getIban() ) )
			->setBic( $request->getBic() )
			->setAccount( $request->getBankAccount() )
			->setBankCode( $request->getBankCode() )
			->setBankName( $request->getBankName() );
		return $bankData->freeze()->assertNoNullFields();
	}

}