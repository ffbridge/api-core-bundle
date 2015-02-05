<?php

namespace Kilix\Bundle\ApiCoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KilixApiCoreExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('kilix_api_core.aglio_bin', $config['aglio_bin']);
        $container->setParameter('kilix_api_core.snowcrash_bin', $config['snowcrash_bin']);
        $container->setParameter('kilix_api_core.apiary2postman_bin', $config['apiary2postman_bin']);
        $container->setParameter('kilix_api_core.blueman_bin', $config['blueman_bin']);
        $container->setParameter('kilix_api_core.default_postman_converter', $config['default_postman_converter']);

        $definition = $container->getDefinition('kilix_api_core.blueprint_manager');
        $definition->addArgument($config['replacements']);
    }
}
