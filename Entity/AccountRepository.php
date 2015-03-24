<?php
/*
* This file is part of the Sulu CMS.
*
* (c) MASSIVE ART WebServices GmbH
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace Massive\Bundle\ContactBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Repository for the Codes, implementing some additional functions
 * for querying objects
 */
class AccountRepository extends NestedTreeRepository
{
    /**
     * Get account by id
     * @param $id
     * @param $contacts
     * @return mixed
     */
    public function findAccountById($id, $contacts = false)
    {
        try {
            $qb = $this->createQueryBuilder('account')
                ->leftJoin('account.categories', 'categories')
                ->leftJoin('categories.translations', 'categoryTranslations')
                ->leftJoin('account.accountAddresses', 'accountAddresses')
                ->leftJoin('accountAddresses.address', 'addresses')
                ->leftJoin('addresses.country', 'country')
                ->leftJoin('addresses.addressType', 'addressType')
                ->leftJoin('account.parent', 'parent')
                ->leftJoin('account.urls', 'urls')
                ->leftJoin('urls.urlType', 'urlType')
                ->leftJoin('account.phones', 'phones')
                ->leftJoin('phones.phoneType', 'phoneType')
                ->leftJoin('account.emails', 'emails')
                ->leftJoin('emails.emailType', 'emailType')
                ->leftJoin('account.notes', 'notes')
                ->leftJoin('account.faxes', 'faxes')
                ->leftJoin('faxes.faxType', 'faxType')
                ->leftJoin('account.bankAccounts', 'bankAccounts')
                ->leftJoin('account.tags', 'tags')
                ->leftJoin('account.termsOfDelivery', 'termsOfDelivery')
                ->leftJoin('account.termsOfPayment', 'termsOfPayment')
                ->leftJoin('account.responsiblePerson', 'responsiblePerson')
                ->leftJoin('account.mainContact', 'mainContact')
                ->leftJoin('account.medias', 'medias')
                ->addSelect('mainContact')
                ->addSelect('categories')
                ->addSelect('categoryTranslations')
                ->addSelect('partial tags.{id, name}')
                ->addSelect('bankAccounts')
                ->addSelect('accountAddresses')
                ->addSelect('addresses')
                ->addSelect('country')
                ->addSelect('addressType')
                ->addSelect('parent')
                ->addSelect('urls')
                ->addSelect('urlType')
                ->addSelect('phones')
                ->addSelect('phoneType')
                ->addSelect('emails')
                ->addSelect('emailType')
                ->addSelect('faxes')
                ->addSelect('faxType')
                ->addSelect('notes')
                ->addSelect('termsOfDelivery')
                ->addSelect('termsOfPayment')
                ->addSelect('responsiblePerson')
                ->addSelect('medias')
                ->where('account.id = :accountId');

            if ($contacts === true) {
                $qb->leftJoin('account.accountContacts', 'accountContacts')
                    ->leftJoin('accountContacts.contact', 'contacts')
                    ->leftJoin('accountContacts.position', 'position')
                    ->addSelect('position')
                    ->addSelect('accountContacts')
                    ->addSelect('contacts');
            }

            $query = $qb->getQuery();
            $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
            $query->setParameter('accountId', $id);

            return $query->getSingleResult();
        } catch (NoResultException $ex) {
            return null;
        }
    }
}
