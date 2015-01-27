<?php

namespace Kilix\Bundle\ApiCoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kilix_api_core');

        $rootNode
            ->children()
                ->arrayNode('replacements')
                    ->fixXmlConfig('replacement', 'replacements')
                    ->defaultValue(array())
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->scalarNode('aglio_bin')->defaultValue('/usr/bin/aglio')->end()
                ->scalarNode('snowcrash_bin')->defaultValue('/usr/local/bin/snowcrash')->end()
                ->scalarNode('apiary2postman_bin')->defaultValue('/usr/local/bin/apiary2postman')->end()
            ->end();

        return $treeBuilder;
    }
}
