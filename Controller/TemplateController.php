<?php

namespace Sulu\Bundle\ContactExtensionBundle\Controller;

use Sulu\Bundle\ContactBundle\Controller\TemplateController as SuluContactTemplateController;

/**
 * Serves templates for sulu contact extension bundle
 */
class TemplateController extends SuluContactTemplateController
{
    /**
     * Returns the financials form for accounts
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function accountFinancialsAction()
    {
        return $this->render('SuluContactExtensionBundle:Template:account.financials.html.twig');
    }
}
