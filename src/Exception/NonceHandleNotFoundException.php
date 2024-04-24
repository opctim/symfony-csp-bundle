<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Exception;

use Exception;

class NonceHandleNotFoundException extends Exception
{
    public function __construct(string $nonceHandle)
    {
        parent::__construct();

        $this->message =
            'Unable to find nonce handle "' . $nonceHandle .
            '". Maybe you forgot to configure it in your directives in the opctim_csp_bundle config?';
    }
}