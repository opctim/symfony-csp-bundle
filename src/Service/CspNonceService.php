<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Service;

use Opctim\CspBundle\Exception\NonceHandleNotFoundException;
use Random\RandomException;

class CspNonceService
{
    private array $nonceTokens = [];


    public function addNonce(string $handle): string
    {
        return $this->nonceTokens[$handle] = $this->createNonceToken();
    }

    /**
     * @throws NonceHandleNotFoundException
     */
    public function getNonce(string $handle): string
    {
        return $this->nonceTokens[$handle] ?? throw new NonceHandleNotFoundException($handle);
    }

    protected function createNonceToken(): string
    {
        try {
            $randomBytes = random_bytes(8);
        } catch (RandomException) {
            $randomBytes = openssl_random_pseudo_bytes(8);
        }

        return substr(
            md5(
                bin2hex(
                    $randomBytes
                )
            ),
            0,
            10
        );
    }
}