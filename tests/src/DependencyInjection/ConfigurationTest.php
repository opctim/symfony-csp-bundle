<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Tests\DependencyInjection;

use Opctim\CspBundle\DependencyInjection\Configuration;
use Opctim\CspBundle\DependencyInjection\OpctimCspBundleExtension;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testConfiguration(): void
    {
        $extension = new OpctimCspBundleExtension();
        $configuration = new Configuration($extension->getAlias());

        $name = $configuration->getConfigTreeBuilder()->getRootNode()->getNode(true)->getName();

        self::assertEquals(
            $extension->getAlias(),
            $name
        );
    }
}