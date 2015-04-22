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

use Sulu\Bundle\AdminBundle\Widgets\WidgetInterface;
use Sulu\Bundle\AdminBundle\Widgets\WidgetException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetEntityNotFoundException;
use Doctrine\ORM\EntityManager;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;

/**
 * Widget to display main contact
 */
class MainContact implements WidgetInterface
{
    protected $em;

    protected $widgetName = 'MainContact';
    protected $accountEntityName;

    public function __construct(EntityManager $em, $accountEntityName)
    {
        $this->em = $em;
        $this->accountEntityName = $accountEntityName;
    }

    /**
     * return name of widget
     *
     * @return string
     */
    public function getName()
    {
        return 'contacts';
    }

    /**
     * returns template name of widget
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluContactExtensionBundle:Widgets:account.main.contact.html.twig';
    }

    /**
     * returns data to render template
     *
     * @param array $options
     * @throws WidgetException
     * @return array
     */
    public function getData($options)
    {
        if (!empty($options) &&
            array_key_exists('account', $options) &&
            !empty($options['account'])
        ) {
            $id = $options['account'];
            $account = $this->em->getRepository(
                $this->accountEntityName
            )->find($id);

            if (!$account) {
                throw new WidgetEntityNotFoundException(
                    'Entity ' . $this->accountEntityName . ' with id ' . $id . ' not found!',
                    $this->widgetName,
                    $id
                );
            }

            return $this->parseMainContact($account);
        } else {
            throw new WidgetParameterException(
                'Required parameter account not found or empty!',
                $this->widgetName,
                'account'
            );
        }
    }

    /**
     * Returns the data needed for the account list-sidebar
     *
     * @param AccountInterface $account
     * @return array
     */
    protected function parseMainContact(AccountInterface $account)
    {
        $contact = $account->getMainContact();

        if ($contact) {
            $data = [];
            $data['id'] = $contact->getId();
            $data['fullName'] = $contact->getFullName();
            $data['phone'] = $contact->getMainPhone();
            $data['email'] = $contact->getMainEmail();

            return $data;
        } else {
            return null;
        }
    }
}
