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

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationProviderInterface;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;

/**
 * Extends account form with financials
 */
class SuluContactExtensionContentNavigation implements ContentNavigationProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNavigationItems(array $options = [])
    {
        // financial infos
        $financials = new ContentNavigationItem('navigation.financials');
        $financials->setAction('financials');
        $financials->setPosition(80);
        $financials->setId('financials');
        $financials->setComponent('accounts/edit/financials@sulucontactextension');
        $financials->setDisplay(['edit']);
        $financials->setDisabled(true);

        return [$financials];
    }
}
