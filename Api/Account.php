<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\ContactBundle\Api;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\ContactBundle\Api\Account as SuluAccount;
use Sulu\Bundle\ContactBundle\Api\Contact;
use Sulu\Bundle\ContactBundle\Entity\Contact as ContactEntity;
use Massive\Bundle\ContactBundle\Entity\TermsOfDelivery as TermsOfDeliveryEntity;
use Massive\Bundle\ContactBundle\Entity\TermsOfPayment as TermsOfPaymentEntity;

/**
 * The Account class which will be exported to the API
 * @ExclusionPolicy("all")
 */
class Account extends SuluAccount
{
    /**
     * Set type
     *
     * @param integer $type
     * @return Account
     */
    public function setType($type)
    {
        $this->entity->setType($type);

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     * @VirtualProperty
     * @SerializedName("type")
     * @Groups({"fullAccount", "partialAccount"})
     */
    public function getType()
    {
        return $this->entity->getType();
    }

    /**
     * Set responsiblePerson
     *
     * @param ContactEntity $responsiblePerson
     * @return Account
     */
    public function setResponsiblePerson(ContactEntity $responsiblePerson = null)
    {
        $this->entity->setResponsiblePerson($responsiblePerson);

        return $this;
    }

    /**
     * Get responsiblePerson
     *
     * @return Account
     * @VirtualProperty
     * @SerializedName("responsiblePerson")
     * @Groups({"fullAccount"})
     */
    public function getResponsiblePerson()
    {
        if ($this->entity->getResponsiblePerson()) {
            return new Contact($this->entity->getResponsiblePerson(), $this->locale);
        }

        return null;
    }

    /**
     * Set termsOfPayment
     *
     * @param TermsOfPaymentEntity $termsOfPayment
     * @return Account
     */
    public function setTermsOfPayment(TermsOfPaymentEntity $termsOfPayment = null)
    {
        $this->entity->setTermsOfPayment($termsOfPayment);

        return $this;
    }

    /**
     * Get termsOfPayment
     *
     * @return TermsOfPaymentEntity
     * @VirtualProperty
     * @SerializedName("termsOfPayment")
     * @Groups({"fullAccount"})
     */
    public function getTermsOfPayment()
    {
        return $this->entity->getTermsOfPayment();
    }

    /**
     * Set termsOfDelivery
     *
     * @param TermsOfDeliveryEntity $termsOfDelivery
     * @return Account
     */
    public function setTermsOfDelivery(TermsOfDeliveryEntity $termsOfDelivery = null)
    {
        $this->entity->setTermsOfDelivery($termsOfDelivery);

        return $this;
    }

    /**
     * Get termsOfDelivery
     *
     * @return TermsOfDeliveryEntity
     * @VirtualProperty
     * @SerializedName("termsOfDelivery")
     * @Groups({"fullAccount"})
     */
    public function getTermsOfDelivery()
    {
        return $this->entity->getTermsOfDelivery();
    }
}
