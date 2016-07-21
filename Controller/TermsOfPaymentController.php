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

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Hateoas\Representation\CollectionRepresentation;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfPayment;
use Sulu\Bundle\ContactExtensionBundle\Exception\TermsAlreadySetException;
use Sulu\Component\Persistence\Repository\ORM\EntityRepository;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TermsOfPaymentController extends RestController implements ClassResourceInterface
{
    /**
     * {@inheritdoc}
     */
    protected static $entityKey = 'termsOfPayments';

    /**
     * Shows a single terms of payment.
     *
     * @Route("/termsofpayments/{id}")
     *
     * @param int $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        $view = $this->responseGetById(
            $id,
            function ($id) {
                return $this->getTermsOfPaymentRepository()->find($id);
            }
        );

        return $this->handleView($view);
    }

    /**
     * Lists all terms of payments.
     * Optional parameter 'flat' calls listAction.
     *
     * @Route("/termsofpayments")
     *
     * @return Response
     */
    public function cgetAction()
    {
        $termsOfPayment = $this->getTermsOfPaymentRepository()->findBy([], ['terms' => 'ASC']);

        $list = new CollectionRepresentation($termsOfPayment, self::$entityKey);

        $view = $this->view($list, 200);

        return $this->handleView($view);
    }

    /**
     * Creates a terms of payment.
     *
     * @Route("/termsofpayments")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        $terms = $request->get('terms');

        try {
            $termsOfPayment = $this->createTermsOfPayment($terms);
            $this->getDoctrine()->getManager()->flush();

            $view = $this->view($termsOfPayment, 200);
        } catch (EntityNotFoundException $enfe) {
            $view = $this->view($enfe->toArray(), 404);
        } catch (RestException $re) {
            $view = $this->view($re->toArray(), 400);
        } catch (TermsAlreadySetException $e) {
            $view = $this->view($e->getMessage(), 400);
        }

        return $this->handleView($view);
    }

    /**
     * Edits the existing terms-of-payment with the given id.
     *
     * @Route("/termsofpayments/{id}")
     *
     * @param int $id
     * @param Request $request
     *
     * @throws EntityNotFoundException
     *
     * @return Response
     */
    public function putAction(Request $request, $id)
    {
        try {
            /** @var TermsOfPayment $termsOfPayment */
            $termsOfPayment = $this->getTermsOfPaymentRepository()->find($id);

            if (!$termsOfPayment) {
                throw new EntityNotFoundException($this->getEntityName(), $id);
            }

            $terms = $request->get('terms');

            $em = $this->getDoctrine()->getManager();
            $this->setTermsToEntity($termsOfPayment, $terms);

            $em->flush();
            $view = $this->view($termsOfPayment, 200);

        } catch (EntityNotFoundException $enfe) {
            $view = $this->view($enfe->toArray(), 404);
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
        } catch (TermsAlreadySetException $e) {
            $view = $this->view($e->getMessage(), 400);
        }

        return $this->handleView($view);
    }

    /**
     * Delete terms-of-payment with the given id.
     *
     * @Route("/termsofpayments/{id}")
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        try {
            $delete = function ($id) {
                /* @var TermsOfPayment $termsOfPayment */
                $termsOfPayment = $this->getTermsOfPaymentRepository()->find($id);

                if (!$termsOfPayment) {
                    throw new EntityNotFoundException($this->getEntityName(), $id);
                }

                $em = $this->getDoctrine()->getManager();
                $em->remove($termsOfPayment);
                $em->flush();
            };

            $view = $this->responseDelete($id, $delete);
        } catch (EntityNotFoundException $enfe) {
            $view = $this->view($enfe->toArray(), 404);
        }

        return $this->handleView($view);
    }

    /**
     * Add or update a bunch of terms of payment.
     *
     * @Route("/termsofpayments")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function patchAction(Request $request)
    {
        try {
            $data = [];

            $i = 0;
            while ($item = $request->get($i)) {
                if (!isset($item['terms'])) {
                    throw new RestException('There is no category-name for the account-category given');
                }

                $data[] = $this->processTerms($item);
                $i++;
            }

            $this->getDoctrine()->getManager()->flush();
            $view = $this->view($data, 200);
        } catch (EntityNotFoundException $enfe) {
            $view = $this->view($enfe->toArray(), 404);
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
        } catch (TermsAlreadySetException $e) {
            $view = $this->view($e->getMessage(), 400);
        }

        return $this->handleView($view);
    }

    /**
     * Helper function for patch action.
     *
     * @param array $item
     *
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     *
     * @return TermsOfPayment
     */
    private function processTerms(array $item)
    {
        if (isset($item['id']) && !empty($item['id'])) {
            /* @var TermsOfPayment $termsOfPayment */
            $termsOfPayment = $this->getTermsOfPaymentRepository()->find($item['id']);

            if ($termsOfPayment == null) {
                throw new EntityNotFoundException($this->getTermsOfPaymentRepository()->getClassName(), $item['id']);
            }

            $this->setTermsToEntity($termsOfPayment, $item['terms']);

            return $termsOfPayment;
        }

        return $this->createTermsOfPayment($item['terms']);
    }

    /**
     * @param string $terms
     *
     * @return TermsOfPayment
     */
    private function createTermsOfPayment($terms)
    {
        $termsOfPayment = new TermsOfPayment();
        $this->setTermsToEntity($termsOfPayment, $terms);

        $this->getDoctrine()->getManager()->persist($termsOfPayment);

        return $termsOfPayment;
    }

    /**
     * @param TermsOfPayment $entity
     * @param string $terms
     *
     * @throws RestException
     * @throws TermsAlreadySetException
     */
    private function setTermsToEntity($entity, $terms)
    {
        if ($terms == null || $terms == '') {
            throw new RestException('Parameter terms not given');
        }

        $termsOfPayment = $this->getTermsOfPaymentRepository()->findByTerms($terms);

        if ($termsOfPayment) {
            throw new TermsAlreadySetException(sprintf('%s already set.', $terms));
        }

        $entity->setTerms($terms);
    }

    /**
     * @return string
     */
    private function getEntityName()
    {
        return $this->getTermsOfPaymentRepository()->getClassName();
    }

    /**
     * @return EntityRepository
     */
    private function getTermsOfPaymentRepository()
    {
        return $this->get('sulu_contact_extension.terms_of_payment_repository');
    }
}
