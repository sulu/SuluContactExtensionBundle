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
    const TYPE_BASIC = 0;
    const TYPE_LEAD = 1;
    const TYPE_CUSTOMER = 2;
    const TYPE_SUPPLIER = 3;

    /**
     * @var integer
     */
    protected $type = self::TYPE_BASIC;

    /**
     * @var Contact
     */
    private $responsiblePerson;

    /**
     * @var TermsOfPayment
     */
    private $termsOfPayment;

    /**
     * @var TermsOfDelivery
     */
    private $termsOfDelivery;

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

    /**
     * Set type
     *
     * @param integer $type
     * @return Account
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return TermsOfPayment
     */
    public function getTermsOfPayment()
    {
        return $this->termsOfPayment;
    }

    /**
     * @param TermsOfPayment $termsOfPayment
     */
    public function setTermsOfPayment($termsOfPayment)
    {
        $this->termsOfPayment = $termsOfPayment;
    }

    /**
     * @return TermsOfDelivery
     */
    public function getTermsOfDelivery()
    {
        return $this->termsOfDelivery;
    }

    /**
     * @param TermsOfDelivery $termsOfDelivery
     */
    public function setTermsOfDelivery($termsOfDelivery)
    {
        $this->termsOfDelivery = $termsOfDelivery;
    }
}
