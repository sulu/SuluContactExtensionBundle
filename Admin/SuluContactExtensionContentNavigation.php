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
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

/**
 * Extends account form with financials
 */
class SuluContactExtensionContentNavigation implements ContentNavigationProviderInterface
{
    /**
     * @var SecurityCheckerInterface
     */
    private $securityChecker;

    public function __construct(SecurityCheckerInterface $securityChecker)
    {
        $this->securityChecker = $securityChecker;
    }

    public function getNavigationItems(array $options = array())
    {
        // financial infos
        $item = new ContentNavigationItem('navigation.financials');
        $item->setAction('financials');
        $item->setId('financials');
        $item->setDisabled(true);
        $item->setComponent('accounts@sulucontact');
        $item->setComponentOptions(array('display' => 'financials'));
        $item->setDisplay(array('edit'));

        return array($item);
    }
}
