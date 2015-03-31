<?php

namespace Massive\Bundle\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Serves templates for massive contact bundle
 */
class TemplateController extends Controller
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
