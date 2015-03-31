<?php

namespace Massive\Bundle\ContactBundle\Controller;

use Sulu\Bundle\ContactBundle\Controller\TemplateController as SuluContactTemplateController;

/**
 * Serves templates for massive contact bundle
 */
class TemplateController extends SuluContactTemplateController
{
    /**
     * Returns the financials form for accounts
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function accountFinancialsAction()
    {
        return $this->render('MassiveContactBundle:Template:account.financials.html.twig');
    }
}
