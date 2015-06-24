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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Sulu\Bundle\AdminBundle\Widgets\WidgetInterface;
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetEntityNotFoundException;
use Sulu\Bundle\ContactBundle\Entity\Contact;

/**
 * Widget to display main account
 */
class MainAccount implements WidgetInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $widgetName = 'MainAccount';

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
     * return name of widget
     *
     * @return string
     */
    public function getName()
    {
        return 'contact-main-account';
    }

    /**
     * returns template name of widget
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluContactExtensionBundle:Widgets:contact.main.account.html.twig';
    }

    /**
     * returns data to render template
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
            array_key_exists('contact', $options) &&
            !empty($options['contact'])
        ) {
            $id = $options['contact'];
            $contact = $this->contactRepository->find($id);

            if (!$contact) {
                throw new WidgetEntityNotFoundException(
                    'Entity ' . $this->contactRepository->getClassName() . ' with id ' . $id . ' not found!',
                    $this->widgetName,
                    $id
                );
            }

            return $this->parseMainAccount($contact);
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
     * @param Contact $contact
     *
     * @return array
     */
    protected function parseMainAccount(Contact $contact)
    {
        $account = $contact->getMainAccount();

        if ($account) {
            $data = [];
            $data['id'] = $account->getId();
            $data['name'] = $account->getName();
            $data['phone'] = $account->getMainPhone();
            $data['email'] = $account->getMainEmail();
            $data['url'] = $account->getMainUrl();

            return $data;
        } else {
            return null;
        }
    }
}
