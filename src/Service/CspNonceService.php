<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Service;

use Exception;
use Opctim\CspBundle\Exception\NonceHandleNotFoundException;

class CspNonceService
{
    private array $nonceTokens = [];


    public function addNonce(string $handle): string
    {
        return $this->nonceTokens[$handle] = $this->nonceTokens[$handle] ?? $this->createNonceToken();
    }

    /**
     * @throws NonceHandleNotFoundException
     */
    public function getNonce(string $handle): string
    {
        $nonceToken = $this->nonceTokens[$handle] ?? null;

        if (!$nonceToken) {
            throw new NonceHandleNotFoundException($handle);
        }

        return $nonceToken;
    }

    protected function createNonceToken(): string
    {
        try {
            $randomBytes = random_bytes(8);
        } catch (Exception) {
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