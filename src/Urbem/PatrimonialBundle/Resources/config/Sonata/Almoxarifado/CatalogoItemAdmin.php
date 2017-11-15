<?php

namespace Urbem\PatrimonialBundle\Resources\config\Sonata\Almoxarifado;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Sonata\AdminBundle\Route\RouteCollection;

use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Urbem\CoreBundle\Entity\Almoxarifado;
use Urbem\CoreBundle\Entity\Administracao;
use Urbem\CoreBundle\Model\Administracao\UnidadeMedidaModel;
use Urbem\CoreBundle\Model\Patrimonial\Almoxarifado\AtributoCatalogoItemModel;
use Urbem\CoreBundle\Model\Patrimonial\Almoxarifado\CatalogoClassificacaoModel;
use Urbem\CoreBundle\Model\Patrimonial\Almoxarifado\ControleEstoqueModel;
use Urbem\CoreBundle\Model\Patrimonial\Almoxarifado\TipoItemModel;
use Urbem\CoreBundle\Resources\config\Sonata\AbstractSonataAdmin as AbstractAdmin;

use Urbem\CoreBundle\Model\Patrimonial\Almoxarifado\CatalogoItemModel;

/**
 * Class CatalogoItemAdmin
 * @package Urbem\PatrimonialBundle\Resources\config\Sonata\Almoxarifado
 */
class CatalogoItemAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'urbem_patrimonial_almoxarifado_catalogo_item';
    protected $baseRoutePattern = 'patrimonial/almoxarifado/catalogo-item';
    protected $includeJs = [
        '/patrimonial/javascripts/almoxarifado/catalogo-classificacao-component.js',
        '/administrativo/javascripts/administracao/atributo-dinamico-component.js',
        '/patrimonial/javascripts/almoxarifado/catalogoItem.js',
    ];
    protected $model = CatalogoItemModel::class;
    protected $datagridValues = array(
        '_page'       => 1,
        '_sort_order' => 'DESC', // sort direction
        '_sort_by' => 'codItem' // field name
    );

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add(
            'get_catalogo_classificacao',
            'get-catalogo-classificacao/' . $this->getRouterIdParameter()
        );

        $collection->add(
            'get_classificacao_atributo',
            'get-classificacao-atributo/'
        );

        $collection->add('autocomplete', 'autocomplete');
        $collection->add('autocomplete_ex_servicos', 'autocomplete-ex-servicos');
        $collection->add('info', '{id}/info');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        /** @var ORM\EntityManager $em */
        $em = $this->modelManager->getEntityManager($this->getClass());

        $fieldOptions['codCatalogo'] = [
            'attr' => ['class' => 'select2-parameters '],
            'class' => Almoxarifado\Catalogo::class,
            'choice_label' => 'descricao',
            'query_builder' => function (ORM\EntityRepository $em) {
                $catalogoItemJoin = 'catalogo.codCatalogo = catalogoItem.codCatalogo';

                $queryBuilder = $em->createQueryBuilder('catalogo');
                $queryBuilder
                    ->join(Almoxarifado\CatalogoItem::class, 'catalogoItem', 'WITH', $catalogoItemJoin)
                    ->where('catalogo.permiteManutencao = true');

                return $queryBuilder;
            }
        ];

        $fieldOptions['codClassificacao'] = [
            'attr' => ['class' => 'select2-parameters '],
            'class' => Almoxarifado\CatalogoClassificacao::class,
            'placeholder' => 'label.selecione',
            'route' => ['name' => 'urbem_patrimonial_almoxarifado_catalogo_classificacao_search']
        ];

        $tipoItemModel = new TipoItemModel($em);
        $fieldOptions['codTipo'] = [
            'attr' => ['class' => 'select2-parameters '],
            'class' => Almoxarifado\TipoItem::class,
            'label' => 'label.catalogoItem.codTipo',
            'placeholder' => 'label.selecione',
            'query_builder' => $tipoItemModel->getTiposItem()
        ];

        $datagridMapper
            ->add('codCatalogo', 'doctrine_orm_callback', [
                'callback' => [$this, 'searchFilter'],
                'label' => 'label.catalogoItem.codCatalogo'
            ], 'entity', $fieldOptions['codCatalogo'])
            ->add('descricao', 'doctrine_orm_callback', [
                'callback' => [$this, 'searchFilter'],
                'label' => 'label.catalogoItem.descricao'
            ])
            ->add('codTipo', 'doctrine_orm_callback', [
                'callback' => [$this, 'searchFilter'],
                'label' => 'label.catalogoItem.codTipo'
            ], 'entity', $fieldOptions['codTipo']);
    }

    /**
     * @param ProxyQuery $queryBuilder
     * @param string $alias
     * @param string $field
     * @param array $data
     * @return bool|void
     */
    public function searchFilter(ProxyQuery $queryBuilder, $alias, $field, array $data)
    {
        if (!$data['value']) {
            return;
        }

        $filter = $this->getDataGrid()->getValues();

        if (!empty($filter['descricao']['value'])) {
            $queryBuilder
                ->andWhere("lower({$alias}.descricao) LIKE lower(:descricao)")
                ->orWhere("lower({$alias}.descricaoResumida) LIKE lower(:descricao)")
                ->setParameter('descricao', "%{$filter['descricao']['value']}%");
        }

        if (!empty($filter['codCatalogo']['value'])) {
            $queryBuilder
                ->andWhere("{$alias}.codCatalogo = :catalogo")
                ->setParameter('catalogo', $filter['codCatalogo']['value']);
        }

        if (!empty($filter['codTipo']['value'])) {
            $queryBuilder
                ->andWhere("{$alias}.codTipo = :tipo")
                ->setParameter('tipo', $filter['codTipo']['value']);
        }

        return true;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setBreadCrumb();

        $listMapper
            ->add('fkAlmoxarifadoCatalogoClassificacao.fkAlmoxarifadoCatalogo', 'text', [
                'label' => 'label.catalogoItem.codCatalogo'])
            ->add('codClassificacao', null, ['label' => 'label.catalogoItem.codClassificacao'])
            ->add('codItem', null, ['label' => 'label.catalogoItem.codigo'])
            ->add('fkAlmoxarifadoTipoItem.descricao', null, ['label' => 'label.catalogoItem.codTipo'])
            ->add('descricao', 'template', [
                'label' => 'label.catalogoItem.descricao',
                'template' => 'PatrimonialBundle:Sonata\Almoxarifado\CatalogoItem\CRUD:list__descricao.html.twig'
            ]);

        $this->addActionsGrid($listMapper);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var ORM\EntityManager $em */
        $em = $this->modelManager->getEntityManager($this->getClass());
        $objectId = $this->getAdminRequestId();
        $edicao = false;
        $boTemMovimentacao = false;
        $boPermiteManutencao = true;

        $this->setBreadCrumb($objectId ? ['id' => $objectId] : []);

        // Dados Catalogo
        $fieldOptions['codCatalogo'] = [
            'attr' => ['class' => 'select2-parameters '],
            'choice_label' => 'descricao',
            'choice_value' => 'codCatalogo',
            'class' => Almoxarifado\Catalogo::class,
            'label' => 'label.catalogoItem.codCatalogo',
            'placeholder' => 'label.selecione'
        ];

        $fieldOptions['codClassificacao'] = [
            'attr' => ['class' => 'select2-parameters '],
            'label' => 'label.catalogoItem.codClassificacao',
            'placeholder' => 'Selecione o Catálogo',
            'multiple' => false,
            'mapped' => false,
            'route' => ['name' => 'urbem_patrimonial_almoxarifado_catalogo_classificacao_search'],
            'req_params' => [
                'codCatalogo' => 'varJsCodCatalogo'
            ]
        ];

        // Dados Item
        $tipoItemModel = new TipoItemModel($em);
        $fieldOptions['fkAlmoxarifadoTipoItem'] = [
            'attr' => ['class' => 'select2-parameters '],
            'choice_label' => 'descricao',
            'label' => 'label.catalogoItem.codTipo',
            'placeholder' => 'label.selecione',
            'class' => Almoxarifado\TipoItem::class,
            'query_builder' => $tipoItemModel->getTiposItem()
        ];

        $unidadeMedidaModel = new UnidadeMedidaModel($em);
        $fieldOptions['fkAdministracaoUnidadeMedida'] = [
            'attr' => ['class' => 'select2-parameters '],
            'choice_label' => 'nomUnidade',
            'label' => 'label.catalogoItem.codGrandeza',
            'placeholder' => 'label.selecione',
            'query_builder' => $unidadeMedidaModel->getUnidadesMedidas(),
            'required' => true,
            'class' => Administracao\UnidadeMedida::class
        ];

        // Estoque
        $fieldOptions['estoqueMinimo'] = [
            'label' => 'label.catalogoItem.estoqueMinimo',
            'required' => false,
            'mapped' => false,
            'attr' => ['class' => 'quantity ']
        ];

        $fieldOptions['pontoPedido'] = [
            'label' => 'label.catalogoItem.pontoPedido',
            'required' => false,
            'mapped' => false,
            'attr' => ['class' => 'quantity ']
        ];

        $fieldOptions['estoqueMaximo'] = [
            'label' => 'label.catalogoItem.estoqueMaximo',
            'required' => false,
            'mapped' => false,
            'attr' => ['class' => 'quantity ']
        ];

        // Atributos
        $catalogoItemModel = new CatalogoItemModel($em);
        $fieldOptions['codAtributo'] = [
            'attr' => ['class' => 'select2-parameters '],
            'class' => Administracao\AtributoDinamico::class,
            'query_builder' => $catalogoItemModel->getAtributoDinamicoQuery(),
            'choice_label' => function (Administracao\AtributoDinamico $atributo) {
                return $atributo->getCodAtributo() . ' - ' . $atributo->getNomAtributo();
            },
            'label' => 'label.catalogoItem.codAtributo',
            'mapped' => false,
            'required' => false,
            'multiple' => true,
        ];

        $fieldOptions['codEstrutural'] = [
            'mapped' => false,
            'required' => false,
        ];

        $fieldOptions['codItem'] = [
            'mapped' => false,
            'required' => false,
        ];

        //Atributos
        $fieldOptions['atributosDinamicos'] = [
            'mapped' => false,
            'required' => false
        ];

        $fieldOptions['descricaoResumida'] = [
            'label' => 'label.catalogoItem.descricaoResumida'
        ];

        $fieldOptions['descricao'] = [
            'label' => 'label.catalogoItem.descricao'
        ];

        $tipo = 'entity';

        $route = $this->getRequest()->get('_sonata_name');
        if (sprintf("%s_create", $this->baseRouteName) == $route) {
            $this->includeJs = [
                '/patrimonial/javascripts/almoxarifado/catalogo-classificacao-component.js',
                '/administrativo/javascripts/administracao/atributo-dinamico-component.js',
                '/patrimonial/javascripts/almoxarifado/catalogoItem.js',
            ];
        }
        if (sprintf("%s_edit", $this->baseRouteName) == $route) {

            /** @var Almoxarifado\CatalogoItem $catalogoItem */
            $catalogoItem = $this->getObject($objectId);

            $boPermiteManutencao = $catalogoItem->getFkAlmoxarifadoCatalogoClassificacao()->getFkAlmoxarifadoCatalogo()->getPermiteManutencao();

            $rsEstoque = $em
                ->getRepository(Almoxarifado\EstoqueMaterial::class)
                ->findOneBy([
                    'codItem' => $catalogoItem->getCodItem(),
                ]);
            $boTemMovimentacao = count($rsEstoque) > 0;

            $fieldOptions['codItem']['data'] = $catalogoItem->getCodItem();

            $catalogoClassificacao = $catalogoItem->getFkAlmoxarifadoCatalogoClassificacao();
            $controleEstoques = $catalogoItem->getFkAlmoxarifadoControleEstoque();

            if (count($controleEstoques) > 0) {
                /** @var Almoxarifado\ControleEstoque $controleEstoque */;
                $fieldOptions['estoqueMinimo']['data'] = floatval($controleEstoques->getEstoqueMinimo());
                $fieldOptions['estoqueMaximo']['data'] = floatval($controleEstoques->getEstoqueMaximo());
                $fieldOptions['pontoPedido']['data'] = floatval($controleEstoques->getPontoPedido());
            }

            if (!$boPermiteManutencao && !$boTemMovimentacao) {
                $fieldOptions['descricao']['attr'] = [
                    'readonly' => 'readonly'
                ];

                $fieldOptions['descricaoResumida']['attr'] = [
                    'readonly' => 'readonly'
                ];

                $fieldOptions['fkAdministracaoUnidadeMedida']['data'] = $this->getSubject()->getFkAdministracaoUnidadeMedida();

                $fieldOptions['fkAlmoxarifadoTipoItem']['data'] = $this->getSubject()->getFkAlmoxarifadoTipoItem();
                $edicao = true;
            } else {
                $fieldOptions['codCatalogo']['query_builder'] = function (ORM\EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('c');
                    $qb->where('c.permiteManutencao = true');

                    return $qb;
                };
            }

            $fieldOptions['codCatalogo']['data'] = $catalogoClassificacao->getFkAlmoxarifadoCatalogo();

            $fieldOptions['codEstrutural']['data'] = $catalogoClassificacao->getCodEstrutural();

            // Processa Atributo Dinâmico
            $arrAtributos = [];
            /** @var Almoxarifado\AtributoCatalogoClassificacao $atributoCatalogoClassificacao */
            foreach ($catalogoItem->getFkAlmoxarifadoAtributoCatalogoItens() as $atributoCatalogoItem) {
                $arrAtributos[] = $atributoCatalogoItem->getFkAdministracaoAtributoDinamico();
            }
            $fieldOptions['codAtributo']['data'] = $arrAtributos;
        }

        $formMapper
            ->with('label.catalogoItem.dadosCatalogo')
            ->add('codCatalogo', 'entity', $fieldOptions['codCatalogo']);
        if (sprintf("%s_edit", $this->baseRouteName) == $route) {
            if (!$boPermiteManutencao && !$boTemMovimentacao) {
                $formMapper
                    ->add('ativo', 'hidden', ['data' => $catalogoItem->getAtivo()])
                    ->add(
                        'textAtivo',
                        'text',
                        [
                            'data' => ($catalogoItem->getAtivo() ? 'Ativo' : 'Inativo'),
                            'mapped' => false,
                            'required' => false,
                            'attr' => [
                                'readonly' => 'readonly'
                            ],
                            'label' => 'label.catalogoItem.status'
                        ]
                    )
                    ;
            } else {
                $formMapper->add('ativo');
            }
        } else {
            $formMapper->add('ativo');
        }
        $formMapper
            ->add('edicao', 'hidden', ['data' => $edicao, 'mapped' => false])
            ->end()
            ->with(
                'label.item.tipoCadastroLoteClassificacao',
                [
                    'class' => 'catalogoClassificacaoContainer'
                ]
            )
            ->add(
                'catalogoClassificacaoPlaceholder',
                'text',
                [
                    'mapped' => false,
                    'required' => false
                ]
            )
            ->end()
            ->with('label.bem.atributo', ['class' => 'atributoDinamicoWith'])
            ->add(
                'atributosDinamicos',
                'text',
                $fieldOptions['atributosDinamicos']
            )
            ->add(
                'codEstrutural',
                'hidden',
                $fieldOptions['codEstrutural']
            )
            ->add('codItem', 'hidden', $fieldOptions['codItem'])
            ->end()
            ->with('label.catalogoItem.dadosItem')
            ->add('fkAlmoxarifadoTipoItem', $tipo, $fieldOptions['fkAlmoxarifadoTipoItem'])
            ->add('descricao', 'text', $fieldOptions['descricao'])
            ->add('descricaoResumida', 'text', $fieldOptions['descricaoResumida'])
            ->add('fkAdministracaoUnidadeMedida', $tipo, $fieldOptions['fkAdministracaoUnidadeMedida'])
            ->end()
            ->with('label.catalogoItem.estoque')
            ->add('estoqueMinimo', 'number', $fieldOptions['estoqueMinimo'])
            ->add('pontoPedido', 'number', $fieldOptions['pontoPedido'])
            ->add('estoqueMaximo', 'number', $fieldOptions['estoqueMaximo'])
            ->end()
            ->with('label.catalogoItem.atributo')
            ->add('codAtributo', 'entity', $fieldOptions['codAtributo'])
            ->end();
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $this->setBreadCrumb(['id' => $this->getAdminRequestId()]);

        $template = 'CoreBundle:Sonata/CRUD:show_custom_value.html.twig';

        $fkAlmoxarifadoControleEstoquesTemplate =
            'PatrimonialBundle:Sonata\Almoxarifado\CatalogoItem\CRUD:show__fkAlmoxarifadoControleEstoques.html.twig';

        // atributo
        $fieldOptions['codAtributo'] = [
        ];

        $showMapper
            ->with('label.catalogoItem.dadosClassificacao')
                ->add('fkAlmoxarifadoCatalogoClassificacao.fkAlmoxarifadoCatalogo', null, [
                    'label' => 'label.catalogoItem.codCatalogo',
                    'template' => $template
                ])
                ->add('fkAlmoxarifadoCatalogoClassificacao', null, [
                    'label' => 'label.catalogoItem.codClassificacao',
                    'template' => $template
                ])
            ->end()
            ->with('label.catalogoItem.dadosItem')
                ->add('fkAlmoxarifadoTipoItem.descricao', null, [
                    'label' => 'label.catalogoItem.codTipo'
                ])
                ->add('descricao', null, [
                    'label' => 'label.catalogoItem.descricao'
                ])
                ->add('descricaoResumida', 'text', [
                    'label' => 'label.catalogoItem.descricaoResumida'
                ])
                ->add('fkAdministracaoUnidadeMedida.fkAdministracaoGrandeza', null, [
                    'label' => 'label.catalogoItem.codGrandeza'
                ])
            ->end()
            ->with('label.catalogoItem.estoque')
                ->add('ativo')
                ->add('fkAlmoxarifadoControleEstoques.estoqueMinimo', null, [
                    'label' => 'label.catalogoItem.estoqueMinimo',
                    'template' => $fkAlmoxarifadoControleEstoquesTemplate
                ])
                ->add('fkAlmoxarifadoControleEstoques.pontoPedido', null, [
                    'label' => 'label.catalogoItem.pontoPedido',
                    'template' => $fkAlmoxarifadoControleEstoquesTemplate
                ])
                ->add('fkAlmoxarifadoControleEstoques.estoqueMaximo', null, [
                    'label' => 'label.catalogoItem.estoqueMaximo',
                    'template' => $fkAlmoxarifadoControleEstoquesTemplate
                ])
                ->add('fkAlmoxarifadoAtributoCatalogoItens', null, [
                    'associated_property' => 'fkAdministracaoAtributoDinamico.nomAtributo',
                    'label' => 'label.catalogoItem.codAtributo'
                ])
            ->end();
    }

    /**
     * @param ErrorElement $errorElement
     * @param Almoxarifado\CatalogoItem $catalogoItem
     */
    public function validate(ErrorElement $errorElement, $catalogoItem)
    {
        /** @var ORM\EntityManager $em */
        $em = $this->modelManager->getEntityManager(Almoxarifado\TipoItem::class);
        $formData = $this->getRequest()->request->get($this->getUniqid());

        /** @var Almoxarifado\TipoItem $tipo */
        $tipo = $em->getRepository(Almoxarifado\TipoItem::class)
            ->find($catalogoItem->getCodTipo());

        $catalogoItem->setFkAlmoxarifadoTipoItem($tipo);

        if (!$this->getAdminRequestId()) {
            if (!empty($formData['codClassificacao'])) {
                list($param['codClassificacao'], $param['codCatalogo']) = explode('~', $formData['codClassificacao']);

                $catalogoClassificacao = $em->getRepository(Almoxarifado\CatalogoClassificacao::class)
                    ->find($param);

                $catalogoItem->setFkAlmoxarifadoCatalogoClassificacao($catalogoClassificacao);
            }
        }
    }

    /**
     * @param Almoxarifado\CatalogoItem $catalogoItem
     */
    public function prePersist($catalogoItem)
    {
        /** @var ORM\EntityManager $em */
        $em = $this->modelManager->getEntityManager($this->getClass());
        $catalogoClassificacaoComponent = $this->request->request->get('catalogoClassificacaoComponent');
        $params = [
            'codEstrutural' => end($catalogoClassificacaoComponent),
            'codCatalogo' => $this->getForm()->get('codCatalogo')->getData()->getCodCatalogo()
        ];

        $catalogoClassificacaoModel = new CatalogoClassificacaoModel($em);
        $catalogoClassificacao = $catalogoClassificacaoModel->findOneBy($params);

        $catalogoItem->setFkAlmoxarifadoCatalogoClassificacao($catalogoClassificacao);
    }

    /**
     * @param Almoxarifado\CatalogoItem $catalogoItem
     */
    public function postPersist($catalogoItem)
    {
        /** @var ORM\EntityManager $em */
        $em = $this->modelManager->getEntityManager(Almoxarifado\TipoItem::class);
        $formData = $this->getRequest()->request->get($this->getUniqid());

        $container = $this->getConfigurationPool()->getContainer();

        if (!empty($formData['estoqueMinimo'])
            && !empty($formData['pontoPedido'])
            && !empty($formData['estoqueMaximo'])
        ) {
            $controleEstoqueModel = new ControleEstoqueModel($em);
            $controleEstoqueModel->createOrUpdateWithCatalogoItem($catalogoItem, $formData);
        }

        /** @var array $atributosDinamicos */
        $atributosDinamicos = $this->request->request->get('atributoDinamico');
        $catalogoItemModel = new CatalogoItemModel($em);
        $catalogoItemModel->saveAlmoxarifadoAtributoCatalogoClassificacaoItemValores($catalogoItem, $atributosDinamicos);

        $atributoCatalogoItemModel = new AtributoCatalogoItemModel($em);
        /** @var ArrayCollection $atributosDinamicos */
        $atributosDinamicos = $this->getForm()->get('codAtributo')->getData();
        /** @var Administracao\AtributoDinamico $atributoDinamico */
        foreach ($atributosDinamicos as $atributoDinamico) {
            $atributoCatalogoItemModel->buildWithCatalogoItem($catalogoItem, $atributoDinamico);
        }
        $container->get('session')->getFlashBag()->clear();
        $container->get('session')->getFlashBag()->add('success', $this->getTranslator()->trans('label.catalogoItem.msgSucesso', array('%codItem%' => (string) $catalogoItem->getCodItem(), '%descricao%' => $catalogoItem->getDescricao())));
        $this->forceRedirect($this->generateUrl('list'));
    }

    /**
     * @param Almoxarifado\CatalogoItem $catalogoItem
     */
    public function preUpdate($catalogoItem)
    {
        /** @var ORM\EntityManager $em */
        $em = $this->modelManager->getEntityManager($this->getClass());
        $catalogoClassificacaoComponent = $this->request->request->get('catalogoClassificacaoComponent');
        $params = [
            'codEstrutural' => end($catalogoClassificacaoComponent),
            'codCatalogo' => $this->getForm()->get('codCatalogo')->getData()->getCodCatalogo()
        ];

        $catalogoClassificacaoModel = new CatalogoClassificacaoModel($em);
        $catalogoClassificacao = $catalogoClassificacaoModel->findOneBy($params);

        $catalogoItem->setFkAlmoxarifadoCatalogoClassificacao($catalogoClassificacao);

        /** @var array $atributosDinamicos */
        $atributosDinamicos = $this->request->request->get('atributoDinamico');
        $catalogoItemModel = new CatalogoItemModel($em);
        $catalogoItemModel->saveAlmoxarifadoAtributoCatalogoClassificacaoItemValores($catalogoItem, $atributosDinamicos);
    }

    /**
     * @param Almoxarifado\CatalogoItem $catalogoItem
     */
    public function postUpdate($catalogoItem)
    {
        /** @var ORM\EntityManager $em */
        $em = $this->modelManager->getEntityManager(Almoxarifado\TipoItem::class);
        $formData = $this->getRequest()->request->get($this->getUniqid());

        if (!empty($formData['estoqueMinimo'])
            || !empty($formData['pontoPedido'])
            || !empty($formData['estoqueMaximo'])
        ) {
            $controleEstoqueModel = new ControleEstoqueModel($em);
            $controleEstoqueModel->createOrUpdateWithCatalogoItem($catalogoItem, $formData);
        }

        /** @var ArrayCollection $atributosDinamicos */
        $atributosDinamicos = $this->getForm()->get('codAtributo')->getData();

        $atributoCatalogoItemModel = new AtributoCatalogoItemModel($em);
        $atributoCatalogoItemModel->clearAllExcept($catalogoItem, $atributosDinamicos);
    }
}
