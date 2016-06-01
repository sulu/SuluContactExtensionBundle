<?php
/*
* This file is part of the Sulu CMS.
*
* (c) MASSIVE ART WebServices GmbH
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Sulu\Bundle\ContactExtensionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder->root('sulu_contact_extension')
            ->children()
                ->arrayNode('account_types')
                    ->useAttributeAsKey('title')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('id')->end()
                            ->scalarNode('name')->end()
                            ->scalarNode('translation')->end()
                            ->arrayNode('tabs')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->defaultValue('false')->end()
                            ->end()
                            ->arrayNode('convertableTo')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->defaultValue('false')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('display_account_active_toggle')->defaultValue(true)->end()
            ->end();

        return $treeBuilder;
    }
}
