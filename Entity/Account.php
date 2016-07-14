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

use Sulu\Bundle\ContactBundle\Entity\AbstractAccount as SuluAccount;
use Sulu\Component\Contact\Model\ContactInterface;

class Account extends SuluAccount
{
    const TYPE_BASIC = 0;
    const TYPE_LEAD = 1;
    const TYPE_CUSTOMER = 2;
    const TYPE_SUPPLIER = 3;

    const DEFAULT_IS_ACTIVE = true;

    /**
     * @var bool
     */
    private $isActive = self::DEFAULT_IS_ACTIVE;

    /**
     * @var int
     */
    protected $type = self::TYPE_BASIC;

    /**
     * @var ContactInterface
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
     * @param ContactInterface $responsiblePerson
     *
     * @return self
     */
    public function setResponsiblePerson(ContactInterface $responsiblePerson = null)
    {
        $this->responsiblePerson = $responsiblePerson;

        return $this;
    }

    /**
     * @return ContactInterface
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
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return self
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }
}
