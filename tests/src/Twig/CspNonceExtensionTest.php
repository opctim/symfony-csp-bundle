<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Tests\Twig;

use Opctim\CspBundle\Service\CspNonceService;
use Opctim\CspBundle\Twig\CspNonceExtension;
use Twig\Test\IntegrationTestCase;

class CspNonceExtensionTest extends IntegrationTestCase
{
    private CspNonceService $nonceService;

    public function setUp(): void
    {
        $this->nonceService = $this->createMock(CspNonceService::class);

        $this->nonceService->method('getNonce')->willReturn('TEST');
    }

    protected function getExtensions(): array
    {
        return [
            new CspNonceExtension($this->nonceService)
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/../../fixtures';
    }

    public function getLegacyTests(): array
    {
        return $this->getTests(null);
    }
}