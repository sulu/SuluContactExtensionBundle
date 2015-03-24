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

use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\AbstractAccount as SuluAccount;

class Account extends SuluAccount
{
    /**
     * @var Contact
     */
    private $responsiblePerson;

    /**
     * Set responsiblePerson
     *
     * @param Contact $responsiblePerson
     * @return Account
     */
    public function setResponsiblePerson(Contact $responsiblePerson = null)
    {
        $this->responsiblePerson = $responsiblePerson;
    }

    /**
     * Get responsiblePerson
     *
     * @return \Sulu\Bundle\ContactBundle\Entity\Contact
     */
    public function getResponsiblePerson()
    {
        return $this->responsiblePerson;
    }
}
