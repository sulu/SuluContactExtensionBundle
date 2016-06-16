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

use Sulu\Bundle\ContactBundle\Entity\Contact as SuluContact;

class Contact extends SuluContact
{
    const TYPE_BASIC = 0;
    const TYPE_CUSTOMER = 1;
    const TYPE_SUPPLIER = 2;

    /**
     * @var int
     */
    protected $type = self::TYPE_BASIC;

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
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
}
