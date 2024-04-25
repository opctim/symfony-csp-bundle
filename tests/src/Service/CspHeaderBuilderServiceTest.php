<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Tests\Service;

use Opctim\CspBundle\Service\CspHeaderBuilderService;
use Opctim\CspBundle\Service\CspNonceService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;

class CspHeaderBuilderServiceTest extends TestCase
{
    public function testService(): void
    {
        $nonceService = new CspNonceService();

        $urlGenerator = $this->createMock(UrlGenerator::class);
        $urlGenerator->method('generate')->willReturn('https://example.com');

        $service = new CspHeaderBuilderService(
            $nonceService,
            $urlGenerator,
            [ 'alwaysThere' ],
            [
                'test1' => [
                    'origin1',
                    'origin2',
                    'nonce(test)',
                ],
                'test2' => [
                    'origin1',
                    'origin2',
                ]
            ],
            [
                'url' => null,
                'route' => 'my-route',
                'chance' => 100
            ]
        );

        $csp = $service->build();

        self::assertEquals(
            'test1 alwaysThere origin1 origin2 nonce-' . $nonceService->getNonce('test') .
            '; test2 alwaysThere origin1 origin2; report-uri https://example.com; report-to csp-endpoint;',
            $csp
        );
    }
}