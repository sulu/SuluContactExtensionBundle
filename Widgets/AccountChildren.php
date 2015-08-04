<?php
/*
  * This file is part of the Sulu CMS.
  *
  * (c) MASSIVE ART WebServices GmbH
  *
  * This source file is subject to the MIT license that is bundled
  * with this source code in the file LICENSE.
  */

namespace Sulu\Bundle\ContactExtensionBundle\Widgets;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use Sulu\Bundle\AdminBundle\Widgets\WidgetInterface;
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetEntityNotFoundException;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactExtensionBundle\Entity\Account;

/**
 * Widget for all children of an accounts
 */
class AccountChildren implements WidgetInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $widgetName = 'ContactAccounts';

    /**
     * @var EntityRepository
     */
    protected $accountRepository;

    /**
     * @param EntityManager $em
     * @param EntityRepository $accountRepository
     */
    public function __construct(
        EntityManager $em,
        EntityRepository $accountRepository
    ) {
        $this->em = $em;
        $this->accountRepository = $accountRepository;
    }

    /**
     * Return name of widget.
     *
     * @return string
     */
    public function getName()
    {
        return 'account-children';
    }

    /**
     * Returns template name of widget.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluContactExtensionBundle:Widgets:account.children.html.twig';
    }

    /**
     * Returns data to render template.
     *
     * @param array $options
     *
     * @throws WidgetEntityNotFoundException
     * @throws WidgetParameterException
     *
     * @return array
     */
    public function getData($options)
    {
        if (!empty($options) &&
            array_key_exists('account', $options) &&
            !empty($options['account'])
        ) {
            $id = $options['account'];
            $accounts = $this->accountRepository->findSubsidiariesById($id);

            return $this->parseAccounts($accounts);
        } else {
            throw new WidgetParameterException(
                'Required parameter account not found or empty!',
                $this->widgetName,
                'account'
            );
        }
    }

    /**
     * Parses the main account data.
     *
     * @param Account[] $accounts
     *
     * @return array
     */
    protected function parseAccounts(array $accounts)
    {
        $length = count($accounts);
        if ($length === 0) {
            return null;
        }

        $data = [];
        foreach ($accounts as $account) {
            $accountAddress = $account->getMainAddress();
            $tmp = [];

            $tmp['id'] = $account->getId();
            $tmp['name'] = $account->getName();
            $tmp['phone'] = $account->getMainPhone();
            $tmp['email'] = $account->getMainEmail();
            $tmp['url'] = $account->getMainUrl();

            if ($accountAddress) {
                $tmp['address']['street'] = $accountAddress->getStreet();
                $tmp['address']['number'] = $accountAddress->getNumber();
                $tmp['address']['zip'] = $accountAddress->getZip();
                $tmp['address']['city'] = $accountAddress->getCity();
                $tmp['address']['country'] = $accountAddress->getCountry()->getName();
            }
            $data[] = $tmp;
        }

        return $data;
    }
}
