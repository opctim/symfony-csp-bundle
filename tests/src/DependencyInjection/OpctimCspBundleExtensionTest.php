<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Tests\DependencyInjection;

use Opctim\CspBundle\DependencyInjection\OpctimCspBundleExtension;
use Opctim\CspBundle\Service\CspHeaderBuilderService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class OpctimCspBundleExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->setDefinition(CspHeaderBuilderService::class, new Definition(CspHeaderBuilderService::class));

        $extension = new OpctimCspBundleExtension();

        $alwaysAdd = [
            'origin'
        ];

        $report = [
            'url' => null,
            'route' => 'my_route',
            'chance' => 100
        ];

        $directives = [
            'test' => [
                'origin',
            ]
        ];

        $extension->load(
            [
                [
                    'always_add' => $alwaysAdd,
                    'report' => $report,
                    'directives' => $directives
                ]
            ],
            $containerBuilder
        );

        $definition = $containerBuilder->getDefinition(CspHeaderBuilderService::class);

        self::assertEquals($definition->getArgument('$alwaysAdd'), $alwaysAdd);
        self::assertEquals($definition->getArgument('$directives'), $directives);
        self::assertEquals($definition->getArgument('$report'), $report);
    }
}