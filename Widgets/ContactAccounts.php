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

/**
 * Widget for all accounts of a contact
 */
class ContactAccounts implements WidgetInterface
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
    protected $contactRepository;

    /**
     * @param EntityManager $em
     * @param EntityRepository $contactRepository
     */
    public function __construct(
        EntityManager $em,
        EntityRepository $contactRepository
    ) {
        $this->em = $em;
        $this->contactRepository = $contactRepository;
    }

    /**
     * Return name of widget
     *
     * @return string
     */
    public function getName()
    {
        return 'contact-accounts';
    }

    /**
     * Returns template name of widget
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluContactExtensionBundle:Widgets:contact.accounts.html.twig';
    }

    /**
     * Returns data to render template
     *
     * @param array $options
     * @throws WidgetEntityNotFoundException
     * @throws WidgetParameterException
     * @return array
     */
    public function getData($options)
    {
        if (!empty($options) &&
            array_key_exists('contact', $options) &&
            !empty($options['contact'])
        ) {
            $id = $options['contact'];
            $contact = $this->contactRepository->findContactWithAccountsById($id);

            if (!$contact) {
                throw new WidgetEntityNotFoundException(
                    'Entity ' . $this->contactRepository->getClassName() . ' with id ' . $id . ' not found!',
                    $this->widgetName,
                    $id
                );
            }

            return $this->parseAccounts($contact->getAccountContacts());
        } else {
            throw new WidgetParameterException(
                'Required parameter contact not found or empty!',
                $this->widgetName,
                'contact'
            );
        }
    }

    /**
     * Parses the main account data
     *
     * @param PersistentCollection $accountsContact
     * @return array
     */
    protected function parseAccounts(PersistentCollection $accountsContact)
    {
        $length = count($accountsContact);
        if ($length > 0) {
            $data = [];
            foreach ($accountsContact as $accountContact) {
                $tmp = [];
                $tmp['id'] = $accountContact->getAccount()->getId();
                $tmp['name'] = $accountContact->getAccount()->getName();
                $tmp['phone'] = $accountContact->getAccount()->getMainPhone();
                $tmp['email'] = $accountContact->getAccount()->getMainEmail();
                $tmp['url'] = $accountContact->getAccount()->getMainUrl();
                $tmp['main'] = $accountContact->getMain();
                $data[] = $tmp;
            }
            return $data;
        }

        return null;
    }
}
