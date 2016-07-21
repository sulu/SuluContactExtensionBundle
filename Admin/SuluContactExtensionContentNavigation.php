<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContactExtensionBundle\Admin;

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationProviderInterface;
use Sulu\Bundle\AdminBundle\Navigation\DisplayCondition;

/**
 * Extends account form with financials.
 */
class SuluContactExtensionContentNavigation implements ContentNavigationProviderInterface
{
    /**
     * @var array
     */
    public $accountTypeConfig;

    /**
     * @param array $accountTypeConfig
     */
    public function setAccountTypesConfig(array $accountTypeConfig)
    {
        $this->accountTypeConfig = $accountTypeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getNavigationItems(array $options = [])
    {
        // Financial infos.
        $financials = new ContentNavigationItem('navigation.financials');
        $financials->setAction('financials');
        $financials->setPosition(80);
        $financials->setId('financials');
        $financials->setComponent('accounts/edit/financials@sulucontactextension');
        $financials->setDisplay(['edit']);

        $conditions = [];

        // Check if financials tab should be shown for given config.
        foreach ($this->accountTypeConfig as $key => $accountType) {
            if (!isset($accountType['tabs'])
                || !isset($accountType['tabs']['financials'])
                || $accountType['tabs']['financials'] === false
            ) {
                $conditions[] = new DisplayCondition('type', DisplayCondition::OPERATOR_NOT_EQUAL, $accountType['id']);
            }
        }

        if (count($conditions) > 0) {
            $financials->setDisplayConditions($conditions);
        }

        return [$financials];
    }
}
