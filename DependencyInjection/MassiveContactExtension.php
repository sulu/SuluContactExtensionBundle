<?php
/*
* This file is part of the Sulu CMS.
*
* (c) MASSIVE ART WebServices GmbH
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Massive\Bundle\ContactBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages bundle configuration for massive contact bundle
 */
class MassiveContactExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        foreach ($container->getExtensions() as $name => $extension) {
            if ($name === 'sulu_admin') {
                $container->prependExtensionConfig(
                    $name,
                    array(
                        'widget_groups' => array(
                            'contact-info' => array(
                                'mappings' => array('sulu-contact-contact-info')
                            ),
                            'account-info' => array(
                                'mappings' => array('sulu-contact-account-info', 'sulu-contact-main-contact')
                            ),
                            'contact-detail' => array(
                                'mappings' => array('sulu-contact-main-account')
                            ),
                            'account-detail' => array(
                                'mappings' => array('sulu-contact-main-contact')
                            )
                        )
                    )
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }
}
