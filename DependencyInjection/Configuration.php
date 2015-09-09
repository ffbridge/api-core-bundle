<?php

namespace Kilix\Bundle\ApiCoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
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
                ->enumNode('default_postman_converter')
                    ->values(array('apiary2postman', 'blueman'))
                    ->defaultValue('blueman')
                ->end()
                ->arrayNode('replacements')
                    ->fixXmlConfig('replacement', 'replacements')
                    ->defaultValue(array())
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->scalarNode('aglio_bin')->defaultValue('/usr/bin/aglio')->end()
                ->scalarNode('blueprint_parser_bin')->defaultValue('/usr/local/bin/drafter')->end()
                ->scalarNode('apiary2postman_bin')->defaultValue('/usr/local/bin/apiary2postman')->end()
                ->scalarNode('blueman_bin')->defaultValue('/usr/local/bin/blueman')->end()
            ->end();

        $this->addContentTypeListenerSection($rootNode);

        return $treeBuilder;
    }

    private function addContentTypeListenerSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('content_type_listener')
                    ->fixXmlConfig('decoder', 'decoders')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('decoders')
                            ->defaultValue(array('json' => 'kilix_api_core.decoder.json'))
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
