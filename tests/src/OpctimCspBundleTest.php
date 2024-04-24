<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Tests;

use Opctim\CspBundle\DependencyInjection\OpctimCspBundleExtension;
use Opctim\CspBundle\OpctimCspBundle;
use PHPUnit\Framework\TestCase;

class OpctimCspBundleTest extends TestCase
{
    public function testGetContainerExtension(): void
    {
        $bundle = new OpctimCspBundle();

        self::assertInstanceOf(OpctimCspBundleExtension::class, $bundle->getContainerExtension());
    }
}