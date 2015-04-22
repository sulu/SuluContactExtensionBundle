<?php
/*
  * This file is part of the Sulu CMS.
  *
  * (c) MASSIVE ART WebServices GmbH
  *
  * This source file is subject to the MIT license that is bundled
  * with this source code in the file LICENSE.
  */

namespace Sulu\Bundle\ContactExtensionBundle\Widgets;

use Sulu\Bundle\AdminBundle\Widgets\WidgetInterface;

/**
 * Widget to display a table
 */
class Table implements WidgetInterface
{
    /**
     * return name of widget
     *
     * @return string
     */
    public function getName()
    {
        return 'table';
    }

    /**
     * returns template name of widget
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluContactExtensionBundle:Widgets:table.html.twig';
    }

    /**
     * returns data to render template
     *
     * @param array $options
     * @return array
     */
    public function getData($options)
    {
        // TODO: fetch contact here - (options contains all request parameters)
        return $options;
    }
}
