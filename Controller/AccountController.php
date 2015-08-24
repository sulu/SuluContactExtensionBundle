<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContactExtensionBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations\Post;
use JMS\Serializer\SerializationContext;
use Sulu\Bundle\ContactBundle\Controller\AccountController as SuluAccountController;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Symfony\Component\HttpFoundation\Request;
use Sulu\Bundle\ContactBundle\Entity\Contact as ContactEntity;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfDelivery as TermsOfDeliveryEntity;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfPayment as TermsOfPaymentEntity;

class AccountController extends SuluAccountController
{
    protected static $termsOfPaymentEntityName = 'SuluContactExtensionBundle:TermsOfPayment';
    protected static $termsOfDeliveryEntityName = 'SuluContactExtensionBundle:TermsOfDelivery';

    /**
     * {@inheritdoc}
     */
    protected function doPost(Request $request)
    {
        $account = parent::doPost($request);

        $account->setType($request->get('type', 0));
        $this->setResponsiblePerson($this->getDoctrine()->getManager(), $account, $request->get('responsiblePerson'));
        $this->processTerms($request, $account);

        return $account;
    }

    /**
     * {@inheritdoc}
     */
    protected function doPut(AccountInterface $account, Request $request)
    {
        parent::doPut($account, $request);

        $this->setResponsiblePerson($this->getDoctrine()->getManager(), $account, $request->get('responsiblePerson'));
        $this->processTerms($request, $account);
    }

    /**
     * {@inheritdoc}
     */
    protected function doPatch(AccountInterface $account, Request $request, ObjectManager $entityManager)
    {
        parent::doPatch($account, $request, $entityManager);

        $this->processTerms($request, $account);
    }

    /**
     * Set responsible person from request data to account
     *
     * @param ObjectManager $em
     * @param AccountInterface $account
     * @param ContactInterface $responsiblePerson
     *
     * @throws EntityNotFoundException
     */
    private function setResponsiblePerson(ObjectManager $em, AccountInterface $account, $responsiblePerson)
    {
        if (!!$responsiblePerson) {
            $id = $responsiblePerson['id'];
            /* @var ContactEntity $contact */
            $contact = $em->getRepository($this->container->getParameter('sulu.model.contact.class'))
                ->find($id);

            if (!$contact) {
                throw new EntityNotFoundException($this->container->getParameter('sulu.model.contact.class'), $id);
            }
            $account->setResponsiblePerson($contact);
        }
    }

    /**
     * Applies the filter parameter and hasNoparent parameter for listbuilder.
     *
     * @param Request $request
     * @param array $filter
     * @param DoctrineListBuilder $listBuilder
     */
    protected function applyRequestParameters(Request $request, $filter, $listBuilder)
    {
        parent::applyRequestParameters($request, $filter, $listBuilder);
        $type = $request->get('type');
        if ($type) {
            $listBuilder->where($this->getFieldDescriptors()['type'], $type);
        }
    }

    /**
     * Converts an account to a different account type
     * @Post("/accounts/{id}")
     *
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postTriggerAction($id, Request $request)
    {
        $action = $request->get('action');
        $em = $this->getDoctrine()->getManager();
        $view = null;

        try {
            switch ($action) {
                case 'convertAccountType':
                    $accountType = $request->get('type');
                    $accountEntity = $this->getDoctrine()
                        ->getRepository($this->getAccountEntityName())
                        ->find($id);

                    if (!$accountEntity) {
                        throw new EntityNotFoundException($accountEntity, $id);
                    }

                    if (!$accountType) {
                        throw new RestException("There is no type to convert to given!");
                    }

                    $this->convertToType($accountEntity, $accountType);
                    $em->flush();

                    // get api entity
                    $accountManager = $this->getAccountManager();
                    $locale = $this->getUser()->getLocale();
                    $acc = $accountManager->getAccount($accountEntity, $locale);

                    $view = $this->view($acc, 200);
                    $view->setSerializationContext(
                        SerializationContext::create()->setGroups(
                            array('fullAccount', 'partialContact', 'partialMedia')
                        )
                    );

                    break;
                default:
                    throw new RestException("Unrecognized action: " . $action);
            }
        } catch (EntityNotFoundException $enfe) {
            $view = $this->view($enfe->toArray(), 404);
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
        }

        return $this->handleView($view);
    }

    /**
     * Converts an account to another account type when allowed
     *
     * @param AccountInterface $account
     * @param $type string representation
     *
     * @throws RestException
     */
    protected function convertToType(AccountInterface $account, $type)
    {
        $config = $this->container->getParameter('sulu_contact_extension.account_types');
        $types = $this->getAccountTypes($config);
        $transitionsForType = $this->getAccountTypeTransitions(
            $config,
            $types,
            array_search($account->getType(), $types)
        );

        if ($type && $this->isTransitionAllowed($transitionsForType, $type, $types)) {
            $account->setType($types[$type]);
        } else {
            throw new RestException("Unrecognized type for type conversion or conversion not allowed:" . $type);
        }
    }

    /**
     * Checks whether transition from one type to another is allowed
     *
     * @param $transitionsForType
     * @param $newAccountType
     * @param $types
     *
     * @return bool
     */
    protected function isTransitionAllowed($transitionsForType, $newAccountType, $types)
    {
        foreach ($transitionsForType as $trans) {
            if ($trans === intval($types[$newAccountType])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns valid transitions for a specific accoun type
     *
     * @param $config
     * @param $types
     * @param $accountTypeName
     *
     * @return array
     */
    protected function getAccountTypeTransitions($config, $types, $accountTypeName)
    {
        $transitions = [];
        foreach ($config[$accountTypeName]['convertableTo'] as $transTypeKey => $transTypeValue) {
            if (!!$transTypeValue) {
                $transitions[] = $types[$transTypeKey];
            }
        }

        return $transitions;
    }

    /**
     * Gets the account types and their numeric representation
     *
     * @param $config
     *
     * @return array
     */
    protected function getAccountTypes($config)
    {
        $types = [];
        foreach ($config as $confType) {
            $types[$confType['name']] = $confType['id'];
        }

        return $types;
    }

    /**
     * Processes terms of delivery and terms of payment for an account
     *
     * @param Request $request
     * @param AccountInterface $account
     *
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     */
    protected function processTerms(Request $request, AccountInterface $account)
    {
        if ($request->get('termsOfPayment') !== null) {
            $id = $request->get('termsOfPayment')['id'];
            /** @var TermsOfPaymentEntity $termsOfPayment */
            $termsOfPayment = $this->getDoctrine()
                ->getRepository(self::$termsOfPaymentEntityName)
                ->find($id);

            if (!$termsOfPayment) {
                throw new EntityNotFoundException(self::$termsOfPaymentEntityName, $id);
            }
            $account->setTermsOfPayment($termsOfPayment);
        }

        if ($request->get('termsOfDelivery') !== null) {
            $id = $request->get('termsOfDelivery')['id'];
            /** @var TermsOfDeliveryEntity $termsOfDelivery */
            $termsOfDelivery = $this->getDoctrine()
                ->getRepository(self::$termsOfDeliveryEntityName)
                ->find($id);
            if (!$termsOfDelivery) {
                throw new EntityNotFoundException(self::$termsOfDeliveryEntityName, $id);
            }
            $account->setTermsOfDelivery($termsOfDelivery);
        }
    }

    protected function initFieldDescriptors()
    {
        parent::initFieldDescriptors();

        $this->fieldDescriptors['type'] = new DoctrineFieldDescriptor(
            'type',
            'type',
            $this->getAccountEntityName(),
            'contact.accounts.type',
            array(),
            true,
            false,
            '',
            '150px'
        );
    }
}
