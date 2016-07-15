<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContactExtensionBundle\Api;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\ContactBundle\Api\Account as SuluAccount;
use Sulu\Bundle\ContactBundle\Api\Contact;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfDelivery as TermsOfDeliveryEntity;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfPayment as TermsOfPaymentEntity;
use Sulu\Component\Contact\Model\ContactInterface;

/**
 * The Account class which will be exported to the API
 * @ExclusionPolicy("all")
 */
class Account extends SuluAccount
{
    /**
     * @param int $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->entity->setType($type);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("type")
     * @Groups({"fullAccount", "partialAccount"})
     *
     * @return int
     */
    public function getType()
    {
        return $this->entity->getType();
    }

    /**
     * @param ContactInterface $responsiblePerson
     *
     * @return self
     */
    public function setResponsiblePerson(ContactInterface $responsiblePerson = null)
    {
        $this->entity->setResponsiblePerson($responsiblePerson);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("responsiblePerson")
     * @Groups({"fullAccount"})
     *
     * @return Contact
     */
    public function getResponsiblePerson()
    {
        if ($this->entity->getResponsiblePerson()) {
            return new Contact($this->entity->getResponsiblePerson(), $this->locale);
        }

        return null;
    }

    /**
     * @param TermsOfPaymentEntity $termsOfPayment
     *
     * @return self
     */
    public function setTermsOfPayment(TermsOfPaymentEntity $termsOfPayment = null)
    {
        $this->entity->setTermsOfPayment($termsOfPayment);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("termsOfPayment")
     * @Groups({"fullAccount"})
     *
     * @return TermsOfPaymentEntity
     */
    public function getTermsOfPayment()
    {
        return $this->entity->getTermsOfPayment();
    }

    /**
     * Set termsOfDelivery
     *
     * @param TermsOfDeliveryEntity $termsOfDelivery
     *
     * @return Account
     */
    public function setTermsOfDelivery(TermsOfDeliveryEntity $termsOfDelivery = null)
    {
        $this->entity->setTermsOfDelivery($termsOfDelivery);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("termsOfDelivery")
     * @Groups({"fullAccount"})
     *
     * @return TermsOfDeliveryEntity
     */
    public function getTermsOfDelivery()
    {
        return $this->entity->getTermsOfDelivery();
    }

    /**
     * @VirtualProperty
     * @SerializedName("isActive")
     * @Groups({"fullAccount"})
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->entity->isActive();
    }
}
