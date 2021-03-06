<?php

namespace Urbem\CoreBundle\Resources\config\Sonata\Filter\Compras;

use Symfony\Component\HttpFoundation\Request;
use Urbem\CoreBundle\Entity\Compras\Mapa;
use Urbem\CoreBundle\Repository\Patrimonio\Compras\MapaRepository;
use Urbem\CoreBundle\Resources\config\Sonata\AbstractSonataAdmin as AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

// service: core.admin.filter.compras_mapa
class MapaAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'urbem_core_filter_licitacao_licitacao';
    protected $baseRoutePattern = 'core/filter/licitacao_licitacao';

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, Mapa::class, $baseControllerName);
    }

    /**
     * @param RouteCollection $routeCollection
     */
    protected function configureRoutes(RouteCollection $routeCollection)
    {
        $routeCollection->clearExcept([]);
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form->add('autocomplete_field', 'autocomplete', [
            'class' => Mapa::class,
            'json_from_admin_code' => $this->code,
            'json_query_builder' => function (MapaRepository $repo, $term, Request $request) {
                return $repo->getByTermAsQueryBuilder($term);
            },
        ]);
    }
}

