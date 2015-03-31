<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\ContactBundle\Admin;

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationInterface;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;

/**
 * Extends account form with financials
 */
class MassiveContactContentNavigation implements ContentNavigationInterface
{
    private $navigation = array();

    public function __construct()
    {
        // financial infos
        $item = new ContentNavigationItem('navigation.financials');
        $item->setAction('financials');
        $item->setId('financials');
        $item->setDisabled(true);
        $item->setGroups(array('account'));
        $item->setComponent('accounts@sulucontact');
        $item->setComponentOptions(array('display' => 'financials'));
        $item->setDisplay(array('edit'));
        $this->navigation[] = $item;

    }

    public function getNavigationItems()
    {
        return $this->navigation;
    }
}
