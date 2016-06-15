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

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\ContactBundle\Api\Contact as SuluContact;

class Contact extends SuluContact
{
    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("type")
     * @Serializer\Groups({"fullContact","partialContact","select"})
     *
     * @return int
     */
    public function getType()
    {
        return $this->entity->getType();
    }

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
}
