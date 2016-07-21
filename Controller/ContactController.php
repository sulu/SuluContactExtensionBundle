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

use Hateoas\Configuration\Exclusion;
use Hateoas\Representation\CollectionRepresentation;
use JMS\Serializer\SerializationContext;
use Sulu\Bundle\ContactBundle\Controller\ContactController as SuluContactController;
use Sulu\Bundle\ContactBundle\Util\IndexComparatorInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\MissingArgumentException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactory;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends SuluContactController
{
    /**
     * List all contacts.
     * Optional parameter 'flat' calls listAction.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $serializationGroups = [];
        $locale = $this->getLocale($request);

        if ($request->get('flat') == 'true') {
            $list = $this->getList($request, $locale);
        } else {
            if ($request->get('bySystem') == true) {
                $contacts = $this->getContactsByUserSystem();
                $serializationGroups[] = 'select';
            } else {
                $contacts = $this->getDoctrine()->getRepository(
                    $this->container->getParameter('sulu.model.contact.class')
                )->findAll();
                $serializationGroups = array_merge(
                    $serializationGroups,
                    static::$contactSerializationGroups
                );
            }
            // Convert to api-contacts.
            $apiContacts = [];
            foreach ($contacts as $contact) {
                $apiContacts[] = $this->getContactManager()->getContact($contact, $locale);
            }

            $exclusion = null;
            if (count($serializationGroups) > 0) {
                $exclusion = new Exclusion($serializationGroups);
            }

            $list = new CollectionRepresentation($apiContacts, self::$entityKey, null, $exclusion, $exclusion);
        }

        $view = $this->view($list, 200);

        // Set serialization groups.
        if (count($serializationGroups) > 0) {
            $view->setSerializationContext(
                SerializationContext::create()->setGroups(
                    $serializationGroups
                )
            );
        }

        return $this->handleView($view);
    }

    /**
     * Creates a new contact.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        try {
            $this->checkArguments($request);
            /** @var ContactInterface $contact */
            $contact = $this->getContactManager()->save(
                $request->request->all()
            );
            $contact->setType($request->get('type', 0));
            $this->getDoctrine()->getManager()->flush();
            
            $apiContact = $this->getContactManager()->getContact(
                $contact,
                $this->getLocale($request)
            );
            $view = $this->view($apiContact, 200);
            $view->setSerializationContext(
                SerializationContext::create()->setGroups(
                    static::$contactSerializationGroups
                )
            );
        } catch (EntityNotFoundException $enfe) {
            $view = $this->view($enfe->toArray(), 404);
        } catch (MissingArgumentException $maex) {
            $view = $this->view($maex->toArray(), 400);
        } catch (RestException $re) {
            $view = $this->view($re->toArray(), 400);
        }

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     *
     * @throws MissingArgumentException
     */
    private function checkArguments(Request $request)
    {
        if ($request->get('firstName') == null) {
            throw new MissingArgumentException($this->container->getParameter('sulu.model.contact.class'), 'username');
        }
        if ($request->get('lastName') === null) {
            throw new MissingArgumentException($this->container->getParameter('sulu.model.contact.class'), 'password');
        }
        if ($request->get('formOfAddress') == null) {
            throw new MissingArgumentException($this->container->getParameter('sulu.model.contact.class'), 'contact');
        }
    }

    /**
     * @return DoctrineFieldDescriptor[]
     */
    protected function getFieldDescriptors()
    {
        parent::getFieldDescriptors();

        $this->fieldDescriptors['type'] = new DoctrineFieldDescriptor(
            'type',
            'type',
            $this->getContactEntityName(),
            'contact.contacts.type',
            array(),
            true,
            false,
            '',
            '150px'
        );

        return $this->fieldDescriptors;
    }

    /**
     * @return string
     */
    protected function getContactEntityName()
    {
        return $this->container->getParameter('sulu.model.contact.class');
    }

    /**
     * Applies the filter parameter and hasNoparent parameter for listbuilder.
     *
     * @param Request $request
     * @param DoctrineListBuilder $listBuilder
     */
    protected function applyRequestParameters(Request $request, DoctrineListBuilder $listBuilder)
    {
        $type = $request->get('type');
        if ($type) {
            $fieldDescriptors = $this->getFieldDescriptors();
            $listBuilder->where($fieldDescriptors['type'], $type);
        }
    }

    /**
     * Returns list for cget.
     *
     * @param Request $request
     * @param string $locale
     *
     * @return ListRepresentation
     */
    private function getList(Request $request, $locale)
    {
        /** @var RestHelperInterface $restHelper */
        $restHelper = $this->getRestHelper();

        /** @var DoctrineListBuilderFactory $factory */
        $factory = $this->get('sulu_core.doctrine_list_builder_factory');

        $listBuilder = $factory->create($this->container->getParameter('sulu.model.contact.class'));
        $restHelper->initializeListBuilder($listBuilder, $this->getFieldDescriptors());
        $this->applyRequestParameters($request, $listBuilder);

        $listResponse = $this->prepareListResponse($request, $listBuilder, $locale);

        return new ListRepresentation(
            $listResponse,
            self::$entityKey,
            'get_contacts',
            $request->query->all(),
            $listBuilder->getCurrentPage(),
            $listBuilder->getLimit(),
            $listBuilder->count()
        );
    }

    /**
     * Prepare list response.
     *
     * @param Request $request
     * @param DoctrineListBuilder $listBuilder
     * @param string $locale
     *
     * @return array
     */
    private function prepareListResponse(Request $request, DoctrineListBuilder $listBuilder, $locale)
    {
        $idsParameter = $request->get('ids');
        $ids = array_filter(explode(',', $idsParameter));
        if ($idsParameter !== null && count($ids) === 0) {
            return [];
        }

        if ($idsParameter !== null) {
            $listBuilder->in($this->fieldDescriptors['id'], $ids);
        }

        $listResponse = $listBuilder->execute();
        $listResponse = $this->addAvatars($listResponse, $locale);

        if ($idsParameter !== null) {
            $comparator = $this->getComparator();
            // the @ is necessary in case of a PHP bug https://bugs.php.net/bug.php?id=50688
            @usort(
                $listResponse,
                function ($a, $b) use ($comparator, $ids) {
                    return $comparator->compare($a['id'], $b['id'], $ids);
                }
            );
        }

        return $listResponse;
    }

    /**
     * Takes an array of contacts and resets the avatar containing the media id with
     * the actual urls to the avatars thumbnail.
     *
     * @param array $contacts
     * @param string $locale
     *
     * @return array
     */
    private function addAvatars($contacts, $locale)
    {
        $ids = array_filter(array_column($contacts, 'avatar'));
        $avatars = $this->get('sulu_media.media_manager')->getFormatUrls($ids, $locale);
        foreach ($contacts as $key => $contact) {
            if (array_key_exists('avatar', $contact)
                && $contact['avatar']
                && array_key_exists($contact['avatar'], $avatars)
            ) {
                $contacts[$key]['avatar'] = $avatars[$contact['avatar']];
            }
        }

        return $contacts;
    }

    /**
     * @return IndexComparatorInterface
     */
    private function getComparator()
    {
        return $this->get('sulu_contact.util.index_comparator');
    }
}
