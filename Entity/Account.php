<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContactExtensionBundle\Entity;

use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\AbstractAccount as SuluAccount;

class Account extends SuluAccount
{
    const TYPE_BASIC = 0;
    const TYPE_LEAD = 1;
    const TYPE_CUSTOMER = 2;
    const TYPE_SUPPLIER = 3;

    /**
     * @var int
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
     * @var bool
     */
    private $isActiveCustomer = true;

    /**
     * @param Contact $responsiblePerson
     *
     * @return self
     */
    public function setResponsiblePerson(Contact $responsiblePerson = null)
    {
        $this->responsiblePerson = $responsiblePerson;
    }

    /**
     * @return Contact
     */
    public function getResponsiblePerson()
    {
        return $this->responsiblePerson;
    }

    /**
     * @param int $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
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

    /**
     * @return bool
     */
    public function isActiveCustomer()
    {
        return $this->isActiveCustomer;
    }

    /**
     * @param bool $isActiveCustomer
     *
     * @return self
     */
    public function setIsActiveCustomer($isActiveCustomer)
    {
        $this->isActiveCustomer = $isActiveCustomer;

        return $this;
    }
}
