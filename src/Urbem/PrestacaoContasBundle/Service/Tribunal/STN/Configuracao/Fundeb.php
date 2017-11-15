<?php

namespace Urbem\PrestacaoContasBundle\Service\Tribunal\STN\Configuracao;

use Sonata\AdminBundle\Form\FormMapper;
use Urbem\PrestacaoContasBundle\Service\TribunalStrategy\ConfiguracaoAbstract;
use Urbem\PrestacaoContasBundle\Service\TribunalStrategy\ConfiguracaoInterface;
use Urbem\PrestacaoContasBundle\Service\TribunalStrategy\TceInterface;
use Symfony\Bridge\Twig\TwigEngine;

class Fundeb extends ConfiguracaoAbstract implements ConfiguracaoInterface
{
    /**
     * @return array
     */
    public function includeJs()
    {
        return [
            '/prestacaocontas/js/Tribunal/STN/fundeb.js'
        ];
    }

    /**
     * @return string
     */
    public function dynamicBlockJs()
    {
        return parent::dynamicBlockJs();
    }

    /**
     * @return array
     */
    public function processParameters()
    {
        $params = parent::processParameters();
        return $params;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $parameters = $this->processParameters();
        dump($parameters);
        return true;
    }

    /**
     * @return array
     */
    public function buildServiceProvider(TwigEngine $templating = null)
    {
        return ['response' => 'ok, tudo certo por aqui, estou dentro de Fundeb'];
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @param \Urbem\PrestacaoContasBundle\Service\TribunalStrategy\TceInterface $objectAdmin
     */
    public function view(FormMapper $formMapper, TceInterface $objectAdmin)
    {
        $formMapper = parent::view($formMapper, $objectAdmin);

        // Conteúdo específico
        $this->relatorioConfiguracao->setCustomBodyTemplate("PrestacaoContasBundle::Tribunal/STN/Configuracao/fundebList.html.twig");

//        dump($this->relatorioConfiguracao, $formMapper);die;
//        $formMapper->with("Conteúdo particular");
//        $formMapper->end();
    }
}