<?php

namespace Edan\DpoEdanBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dpo_edan');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->scalarNode('edan_active')->defaultValue('1')->end()
                ->scalarNode('edan_url')->defaultValue('http://edan.si.edu')->end()
                ->scalarNode('edan_app_id')->end()
                ->scalarNode('edan_auth_token')->end()
                ->scalarNode('edan_version')->defaultValue('v1.1')->end()
                ->scalarNode('edan_search_endpoint')->defaultValue('metadata/v1.1/metadata/search.htm')->end()
                ->scalarNode('edan_content_endpoint')->defaultValue('content/v1.1/content/getContent.htm')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
