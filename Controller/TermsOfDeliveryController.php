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
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactExtensionBundle\Exception\TermsAlreadySetException;
use Sulu\Component\Persistence\Repository\ORM\EntityRepository;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TermsOfDeliveryController extends RestController implements ClassResourceInterface
{
    /**
     * {@inheritdoc}
     */
    protected static $entityKey = 'termsOfDeliveries';

    /**
     * Shows a single terms of delivery.
     *
     * @Route("/termsofdeliveries/{id}")
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
                return $this->getTermsOfDeliveryRepository()->find($id);
            }
        );

        return $this->handleView($view);
    }

    /**
     * Lists all terms of deliveries.
     * Optional parameter 'flat' calls listAction.
     *
     * @Route("/termsofdeliveries")
     *
     * @return Response
     */
    public function cgetAction()
    {
        $termsOfDelivery = $this->getTermsOfDeliveryRepository()->findBy([], ['terms' => 'ASC']);

        $list = new CollectionRepresentation($termsOfDelivery, self::$entityKey);

        $view = $this->view($list, 200);

        return $this->handleView($view);
    }

    /**
     * Creates a terms of delivery.
     *
     * @Route("/termsofdeliveries")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        $terms = $request->get('terms');

        try {
            if ($terms == null) {
                throw new RestException('There is no term-name for the term-of-delivery given');
            }

            $em = $this->getDoctrine()->getManager();
            $termsOfDelivery = new TermsOfDelivery();
            $this->setTermsToEntity($termsOfDelivery, $terms);

            $em->persist($termsOfDelivery);
            $em->flush();

            $view = $this->view($termsOfDelivery, 200);
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
     * Edits the existing terms-of-delivery with the given id.
     *
     * @Route("/termsofdeliveries/{id}")
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
            /** @var TermsOfDelivery $termsOfDelivery */
            $termsOfDelivery = $this->getTermsOfDeliveryRepository()->find($id);

            if (!$termsOfDelivery) {
                throw new EntityNotFoundException($this->getEntityName(), $id);
            }

            $terms = $request->get('terms');

            if ($terms == null || $terms == '') {
                throw new RestException('Parameter terms not given');
            }

            $em = $this->getDoctrine()->getManager();
            $this->setTermsToEntity($termsOfDelivery, $terms);

            $em->flush();
            $view = $this->view($termsOfDelivery, 200);

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
     * Delete terms-of-delivery with the given id.
     *
     * @Route("/termsofdeliveries/{id}")
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        try {
            $delete = function ($id) {
                /* @var TermsOfDelivery $termsOfDelivery */
                $termsOfDelivery = $this->getTermsOfDeliveryRepository()->find($id);

                if (!$termsOfDelivery) {
                    throw new EntityNotFoundException($this->getEntityName(), $id);
                }

                $em = $this->getDoctrine()->getManager();
                $em->remove($termsOfDelivery);
                $em->flush();
            };

            $view = $this->responseDelete($id, $delete);
        } catch (EntityNotFoundException $enfe) {
            $view = $this->view($enfe->toArray(), 404);
        }

        return $this->handleView($view);
    }

    /**
     * Add or update a bunch of terms of delivery.
     *
     * @Route("/termsofdeliveries")
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
     * @return TermsOfDelivery
     */
    private function processTerms(array $item)
    {
        $termsOfDelivery = null;

        if (isset($item['id']) && !empty($item['id'])) {
            /* @var TermsOfDelivery $termsOfDelivery */
            $termsOfDelivery = $this->getTermsOfDeliveryRepository()->find($item['id']);

            if ($termsOfDelivery == null) {
                throw new EntityNotFoundException($this->getTermsOfDeliveryRepository()->getClassName(), $item['id']);
            }

            $this->setTermsToEntity($termsOfDelivery, $item['terms']);

            return $termsOfDelivery;
        }

        $termsOfDelivery = new TermsOfDelivery();
        $this->setTermsToEntity($termsOfDelivery, $item['terms']);
        $this->getDoctrine()->getManager()->persist($termsOfDelivery);

        return $termsOfDelivery;
    }

    /**
     * @param TermsOfDelivery $entity
     * @param string $terms
     *
     * @throws TermsAlreadySetException
     */
    private function setTermsToEntity($entity, $terms)
    {
        $termsOfDelivery = $this->getTermsOfDeliveryRepository()->findByTerms($terms);

        if ($termsOfDelivery) {
            throw new TermsAlreadySetException(sprintf('%s already set.', $terms));
        }

        $entity->setTerms($terms);
    }

    /**
     * @return string
     */
    private function getEntityName()
    {
        return $this->getTermsOfDeliveryRepository()->getClassName();
    }

    /**
     * @return EntityRepository
     */
    private function getTermsOfDeliveryRepository()
    {
        return $this->get('sulu_contact_extension.terms_of_delivery_repository');
    }
}
