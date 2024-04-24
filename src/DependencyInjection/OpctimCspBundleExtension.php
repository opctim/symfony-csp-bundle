<?php

namespace Opctim\CspBundle\DependencyInjection;

use Opctim\CspBundle\Service\CspHeaderBuilderService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class OpctimCspBundleExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration($this->getAlias());
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config/'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $configs = $this->processConfiguration($configuration, $configs);

        $parser = $container->getDefinition(CspHeaderBuilderService::class);

        $parser->setArgument('alwaysAdd', $configs['always_add']);
        $parser->setArgument('directives', $configs['directives']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'opctim_csp_bundle';
    }
}
