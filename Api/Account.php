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

/**
 * The Account class which will be exported to the API
 * @ExclusionPolicy("all")
 */
class Account extends SuluAccount
{
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
}
