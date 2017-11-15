<?php

namespace Urbem\PatrimonialBundle\Resources\config\Sonata\Licitacao;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Urbem\CoreBundle\Entity\Licitacao\Comissao;
use Urbem\CoreBundle\Entity\Licitacao\ComissaoMembros;
use Urbem\CoreBundle\Entity\Licitacao\TipoComissao;
use Urbem\CoreBundle\Entity\Normas\Norma;
use Urbem\CoreBundle\Entity\SwCgm;
use Urbem\CoreBundle\Helper\DateTimeMicrosecondPK;
use Urbem\CoreBundle\Model\Patrimonial\Licitacao\ComissaoModel;
use Urbem\CoreBundle\Resources\config\Sonata\AbstractSonataAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ComissaoAdmin extends AbstractSonataAdmin
{
    protected $baseRouteName = 'urbem_patrimonial_licitacao_comissao';
    protected $baseRoutePattern = 'patrimonial/licitacao/comissao';
    protected $fkLicitacaoComissao = [];
    protected $exibirBotaoExcluir = false;

    protected $includeJs = [
        '/patrimonial/javascripts/licitacao/norma-vigencia.js',
    ];

    protected function createFkLicitacaoComissao(Comissao $comissao)
    {
        /** @var $comissaoMembros ComissaoMembros */
        foreach ($comissao->getFkLicitacaoComissaoMembros() as $comissaoMembros) {
            if (null == $comissaoMembros->getCodComissao()) {
                $this->fkLicitacaoComissao[] = clone $comissaoMembros;
                $comissao->removeFkLicitacaoComissaoMembros($comissaoMembros);
            }
        }
    }

    /**
     * @param ErrorElement $errorElement
     * @param Comissao $comissao
     */
    public function validate(ErrorElement $errorElement, $comissao)
    {
        $entityManager = $this->getModelManager()->getEntityManager($this->getClass());
        $anoAtual = (int) $this->getExercicio();


        $route = $this->getRequest()->get('_sonata_name');
        if (null != $route) {
            if (is_null($comissao->getFkNormasNorma()->getFkNormasNormaDataTermino()->getDtTermino())) {
                $message = $this->trans('comissaoVigencia.errors.vigenciaNula', [], 'validators');
                $errorElement->with('vigencia')->addViolation($message)->end();
            } else {
                $dataTermino = (int) $comissao->getFkNormasNorma()->getFkNormasNormaDataTermino()->getDtTermino()->format("Y");
                if ($anoAtual > $dataTermino) {
                    $message = $this->trans('comissaoVigencia.errors.vigenciaDataExpirada', [], 'validators');
                    $errorElement->with('vigencia')->addViolation($message)->end();
                }
            }
        }

        $comissaoModel = new ComissaoModel($entityManager);
        $valida = $comissaoModel->validaComissao($comissao, $this->fkLicitacaoComissao);
        $validaComissaoVigencia = $comissaoModel->validaComissaoVigencia($comissao, $this->fkLicitacaoComissao, $anoAtual);

        if ($validaComissaoVigencia) {
            $message = $this->trans($validaComissaoVigencia, [], 'validators');
            $errorElement->with('fkLicitacaoComissaoMembros')->with('vigencia')->addViolation($message)->end();
        }

        if ($valida) {
            $message = $this->trans($valida, [], 'validators');
            $errorElement->with('fkLicitacaoComissaoMembros')->with('fkLicitacaoTipoMembro')->addViolation($message)->end();
        };
    }
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('codComissao', null, ['label' => 'label.patrimonial.licitacao.codComissao'])
            ->add('fkLicitacaoTipoComissao', null, [
                'label' => 'label.patrimonial.licitacao.tipoComissao'
            ], 'entity', [
                'class' => 'CoreBundle:Licitacao\TipoComissao',
                'choice_label' => function ($codComissao) {
                    return $codComissao->getDescricao();
                }
            ])
            ->add(
                'fkNormasNorma',
                'doctrine_orm_choice',
                array(
                    'label' => 'label.patrimonial.licitacao.norma',
                ),
                'autocomplete',
                array(
                    'class' => Norma::class,
                    'route' => array(
                        'name' => 'patrimonio_licitacao_comissao_licitacao_get_normas'
                    ),
                    'mapped' => true,
                )
            )
            ->add('ativo', null, ['label' => 'Ativo'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setBreadCrumb();

        $comissao = $this->fkLicitacaoComissao;

        $listMapper
            ->add('codComissao', null, ['label' => 'label.patrimonial.licitacao.codComissao'])
            ->add('fkLicitacaoTipoComissao', null, array(
                'associated_property' => 'descricao',
                'label' => 'label.patrimonial.licitacao.tipoComissao',
            ))
            ->add('fkNormasNorma', null, ['label' => 'label.patrimonial.licitacao.norma'])
            ->add('ativo', null, ['label' => 'Ativo'])
            ->add('fkNormasNorma.fkNormasNormaDataTermino.dtTermino', null, ['label' => 'label.vigencia'])
            ->add('fkLicitacaoComissaoMembros', 'customField', ['label' => 'label.presidente', 'template' => 'PatrimonialBundle:Sonata/Licitacao/Comissao/CRUD:comissaoPresidente.html.twig'])
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array('template' => 'CoreBundle:Sonata/CRUD:list__action_show.html.twig'),
                    'edit' => array('template' => 'CoreBundle:Sonata/CRUD:list__action_edit.html.twig'),
                    'ativar' => array('template' => 'PatrimonialBundle:Sonata/Licitacao/Comissao/CRUD:list__action_ativar_inativar.html.twig'),
                )
            ));
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $id = $this->getAdminRequestId();
        $this->setBreadCrumb($id ? ['id' => $id] : []);

        $fieldOptions['dataComissao'] = [
            'required' => false,
            'label' => 'label.patrimonial.licitacao.dadosDesignacaoComissao',
            'mapped' => false,
            'attr' => [
                'readonly' => true
            ]
        ];

        $fieldOptions['vigencia'] = [
            'required' => false,
            'label' => 'label.patrimonial.licitacao.vigencia',
            'attr' => [
                'readonly' => true
            ],
            'mapped' => false
        ];

        $formMapper
            ->add('fkLicitacaoTipoComissao', 'entity', ['class' => 'CoreBundle:Licitacao\TipoComissao',
                'choice_label' => function ($codComissao) {
                    return $codComissao->getDescricao();
                },
                'placeholder' => 'Selecione',
                'label' => 'label.patrimonial.licitacao.tipoComissao',
                'required' => true,
                'attr' => [
                    'class' => 'select2-parameters '
                ]
            ])
            ->add(
                'fkNormasNorma',
                'autocomplete',
                [
                    'label' => 'label.patrimonial.licitacao.norma',
                    'multiple' => false,
                    'attr' => ['class' => 'select2-parameters '],
                    'class' => Norma::class,
                    'json_from_admin_code' => $this->code,
                    'json_query_builder' => function (EntityRepository $repo, $term, Request $request) {
                        return $repo->createQueryBuilder('o')
                            ->where('lower(o.nomNorma) LIKE lower(:nomNorma)')
                            ->orWhere('o.numNorma = :codNorma')
                            ->setParameter('nomNorma', "%{$term}%")
                            ->setParameter('codNorma', $term);
                    },
                    'required' => true,
                ]
            )
            ->add('dataComissao', 'text', $fieldOptions['dataComissao'])
            ->add('vigencia', 'text', $fieldOptions['vigencia'])
            ->end()
            ->with('label.patrimonial.licitacao.dadosMembrosComissao')
            ->add(
                'fkLicitacaoComissaoMembros',
                'sonata_type_collection',
                [
                    'required' => true, // alterado (21/02/17) de false para true, para validação HTML5 [necessário setar os parâmetros tbm no arquivo 'Urbem\PatrimonialBundle\Resources\config\Sonata\Licitacao\ComissaoMembrosAdmin.php']
                    'label' => false
                ],
                [
                    'edit' => 'inline'
                ],
                null
            )
            ->end();

        $comissao = $this->getSubject();
        /** @var EntityManager $entityManager */
        $entityManager = $this->modelManager->getEntityManager($this->getClass());
        $formMapper->getFormBuilder()->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formMapper, $comissao, $entityManager) {
                $data = $event->getData();
                $tipoComissao = '';
                if ($data['fkLicitacaoTipoComissao'] != '') {
                    $findTipoComissao = ['codTipoComissao' => $data['fkLicitacaoTipoComissao']];
                    $tipoComissao = $entityManager->getRepository(TipoComissao::class)->findOneBy($findTipoComissao);
                }
                $session = new Session();
                $session->set('licitacaoTipoComissao', $data['fkLicitacaoTipoComissao']);
                $form = $event->getForm();
                $formOptions = [
                    'class' => 'CoreBundle:Licitacao\TipoComissao',
                    'choice_label' => function ($codComissao) {
                        return $codComissao->getDescricao();
                    },
                    'data' => $tipoComissao,
                    'placeholder' => 'Selecione',
                    'label' => 'label.patrimonial.licitacao.tipoComissao',
                    'required' => true,
                    'attr' => [
                        'class' => 'select2-parameters '
                    ]
                ];

                $form->add('fkLicitacaoTipoComissao', 'entity', $formOptions);
            }
        );
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $this->setBreadCrumb(['id' => $this->getAdminRequestId()]);

        $comissao = $this->getSubject();
        $this->data['fkLicitacaoComissaoMembros'] = $comissao->getFkLicitacaoComissaoMembros();

        $showMapper
            ->add('codComissao', null, ['label' => 'label.patrimonial.licitacao.codComissao'])
            ->add('fkLicitacaoTipoComissao.descricao', null, ['label' => 'label.patrimonial.licitacao.finComissao'])
            ->add('fkNormasNorma', null, ['label' => 'label.patrimonial.licitacao.norma'])
            ->add('fkNormasNorma.dtAssinatura', null, ['label' => 'label.patrimonial.licitacao.dtDesignacao'])
            ->add('fkNormasNorma.fkNormasNormaDataTermino', null, ['label' => 'label.vigencia'])
            ->add('ativo')
            ->add(
                'fkLicitacaoComissaoMembros',
                null,
                [
                    'label' => 'label.patrimonial.licitacao.comissaoMembros',
                    'template' => 'PatrimonialBundle:Sonata/Licitacao/Comissao/CRUD:comissaoMembros.html.twig'
                ]
            );
    }

    /**
     * @param Comissao $comissao
     */
    public function prePersist($comissao)
    {
        $this->createFkLicitacaoComissao($comissao);
    }

    /**
     * @param Comissao $comissao
     */
    public function postPersist($comissao)
    {
        $em = $this->configurationPool->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var  $comissaoMembros ComissaoMembros */
        foreach ($this->fkLicitacaoComissao as $comissaoMembros) {
            $comissaoMembros->setFkLicitacaoComissao($comissao);
            $em->persist($comissaoMembros);
        }

        $em->flush();
    }

    /**
     * @param Comissao $comissao
     */
    public function preUpdate($comissao)
    {
        $entityManager = $this->getConfigurationPool()
            ->getContainer()
            ->get('doctrine')
            ->getManager();

        foreach ($this->getForm()->get('fkLicitacaoComissaoMembros') as $comissaoMembros) {
            if ($comissaoMembros->get('_delete')->getData()) {
                $comissao->removeFkLicitacaoComissaoMembros($comissaoMembros->getData());
                $entityManager->remove($comissaoMembros->getData());
            }
        }
        $this->createFkLicitacaoComissao($comissao);
    }

    /**
     * @param Comissao $comissao
     */
    public function postUpdate($comissao)
    {
        $this->postPersist($comissao);
    }
}
