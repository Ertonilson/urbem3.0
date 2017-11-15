<?php

namespace Urbem\RedeSimplesBundle\Controller;

use Urbem\CoreBundle\Controller\BaseController;

class HomeController extends BaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->setBreadCrumb();

        return $this->render('RedeSimplesBundle::Home/index.html.twig');
    }
}

