<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\ContactBundle\Contact;

use Sulu\Bundle\ContactBundle\Contact\AccountFactory as SuluAccountFactory;
use Massive\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Massive\Bundle\ContactBundle\Api\Account as ApiAccount;

/**
 * Override for account factory
 */
class AccountFactory extends SuluAccountFactory
{
    /**
     * {@inheritdoc}
     */
    public function create()
    {
     return new Account();
    }

    /**
     * {@inheritdoc}
     */
    public function createApiEntity(AccountInterface $account, $locale)
    {
     return new ApiAccount($account, $locale);
    }
}
