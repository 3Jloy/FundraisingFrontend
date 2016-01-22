<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\UseCases\ValidateEmail;

use WMDE\Fundraising\Frontend\DataAccess\InternetDomainNameValidator;
use WMDE\Fundraising\Frontend\Validation\MailValidator;

/**
 * TODO: as is, this is a rather empty partition that can just as well be removed
 *
 * @licence GNU GPL v2+
 */
class ValidateEmailUseCase {

	public function validateEmail( string $email ): bool {
		return ( new MailValidator( new InternetDomainNameValidator() ) )->validate( $email );
	}

}
