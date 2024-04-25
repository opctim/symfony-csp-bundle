<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Tests\Service;

use Opctim\CspBundle\Exception\NonceHandleNotFoundException;
use Opctim\CspBundle\Service\CspNonceService;
use PHPUnit\Framework\TestCase;

class CspNonceServiceTest extends TestCase
{
    public function test(): void
    {
        $service = new CspNonceService();

        $nonce = $service->addNonce('test');

        self::assertRegExp('/[a-f0-9]{10}/', $nonce);

        self::assertEquals($nonce, $service->getNonce('test'));

        self::expectException(NonceHandleNotFoundException::class);

        $service->getNonce('non-existent');
    }

    public function testExisting(): void
    {
        $service = new CspNonceService();

        $service->addNonce('test');

        self::assertEquals(
            $service->addNonce('test'),
            $service->addNonce('test')
        );
    }
}