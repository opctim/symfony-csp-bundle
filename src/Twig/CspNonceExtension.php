<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Twig;

use Opctim\CspBundle\Service\CspNonceService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CspNonceExtension extends AbstractExtension
{
    private CspNonceService $cspNonceService;

    public function __construct(
        CspNonceService $cspNonceService
    )
    {
        $this->cspNonceService = $cspNonceService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('csp_nonce', [ $this->cspNonceService, 'getNonce' ])
        ];
    }
}