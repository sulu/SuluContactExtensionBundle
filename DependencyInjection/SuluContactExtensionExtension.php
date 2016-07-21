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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages bundle configuration for sulu contact extension bundle.
 */
class SuluContactExtensionExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('sulu_contact')) {
            $this->prependSuluContactConfig($container);
        }

        if ($container->hasExtension('sulu_admin')) {
            $this->prependSuluAdminConfig($container);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter(
            'sulu_contact_extension.account_types',
            $config['account_types']
        );

        $container->setParameter(
            'sulu_contact_extension.contact_types',
            $config['contact_types']
        );

        $container->setParameter(
            'sulu_contact_extension.display_account_active_toggle',
            $config['display_account_active_toggle']
        );
    }

    /**
     * Prepends config for sulu_admin.
     *
     * @param ContainerBuilder $container
     */
    private function prependSuluAdminConfig(ContainerBuilder $container)
    {
        $container->prependExtensionConfig(
            'sulu_admin',
            [
                'widget_groups' => [
                    'contact-info' => [
                        'mappings' => [
                            'sulu-contact-contact-info',
                            'sulu-contact-accounts',
                        ],
                    ],
                    'account-info' => [
                        'mappings' => [
                            'sulu-contact-account-info',
                            'sulu-contact-main-contact',
                            'sulu-contact-account-children',
                        ],
                    ],
                    'contact-detail' => [
                        'mappings' => ['sulu-contact-accounts'],
                    ],
                    'account-detail' => [
                        'mappings' => ['sulu-contact-main-contact'],
                    ],
                ],
            ]
        );
    }

    /**
     * Prepends config for sulu_contact.
     *
     * @param ContainerBuilder $container
     */
    private function prependSuluContactConfig(ContainerBuilder $container)
    {
        $container->prependExtensionConfig(
            'sulu_contact',
            [
                'objects' => [
                    'contact' => [
                        'model' => 'Sulu\Bundle\ContactExtensionBundle\Entity\Contact',
                        'repository' => 'Sulu\Bundle\ContactExtensionBundle\Entity\ContactRepository',
                    ],
                ],
            ]
        );
    }
}
