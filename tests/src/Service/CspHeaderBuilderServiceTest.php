<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Tests\Service;

use Opctim\CspBundle\Service\CspHeaderBuilderService;
use Opctim\CspBundle\Service\CspNonceService;
use PHPUnit\Framework\TestCase;

class CspHeaderBuilderServiceTest extends TestCase
{
    public function testService(): void
    {
        $nonceService = new CspNonceService();

        $service = new CspHeaderBuilderService(
            $nonceService,
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
            ]
        );

        $csp = $service->build();

        self::assertEquals('test1 alwaysThere origin1 origin2 nonce-' . $nonceService->getNonce('test') . '; test2 alwaysThere origin1 origin2;', $csp);
    }
}