<?php

namespace Urbem\PortalServicosBundle\Controller;

use Urbem\CoreBundle\Controller\BaseController as Controller;

/**
 * Class HomeController
 *
 * @package Urbem\PortalServicosBundle\Controller
 */
class HomeController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->setBreadCrumb();

        $folderAdmimBundle = $this->container->getParameter('administrativobundle');

        return $this->render(
            'PortalServicosBundle:Home:index.html.twig',
            [
                'ultimasNoticiasCNM' => $this->getUltimasNoticiasCNM(),
                'usuario' => $this->get('security.token_storage')->getToken()->getUser(),
                'profilePictureDir' => $folderAdmimBundle['usuarioShow']
            ]
        );
    }

    /**
     * @return mixed|null
     */
    protected function getUltimasNoticiasCNM()
    {
        $fileName = "ultimasNoticiasCNM.txt";
        $urlUltimasNoticiasCNM = $this->getParameter("url_ultimas_noticias_cnm");

        $serviceExternalData = $this->get('gestor_external_content');

        list($domXML, $contentDOMXPath) = $serviceExternalData->getContentExternalData($urlUltimasNoticiasCNM, $fileName);

        $content = $this->parseUltimasNoticiasCNM($domXML, $contentDOMXPath);

        return $content ? str_replace("<a href", "<a target='blank' href", $content) : null;
    }

    /**
     * @param \DOMDocument $dom
     * @param \DOMXPath $domxPath
     * @return null|string
     */
    protected function parseUltimasNoticiasCNM(\DOMDocument $dom, \DOMXPath $domxPath)
    {
        $tags = $domxPath->query('//section[@class="outras_noticias"]');
        if ($tags->length) {
            return $dom->saveHTML($tags->item(0));
        }

        return null;
    }
}
