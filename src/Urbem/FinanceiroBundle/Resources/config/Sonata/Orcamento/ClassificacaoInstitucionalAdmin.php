<?php

namespace Urbem\FinanceiroBundle\Resources\config\Sonata\Orcamento;

use Urbem\CoreBundle\Entity\Economico\ResponsavelTecnico;
use Urbem\CoreBundle\Entity\Orcamento\Entidade;
use Urbem\CoreBundle\Entity\Orcamento\EntidadeLogotipo;
use Urbem\CoreBundle\Entity\Orcamento\UsuarioEntidade;
use Urbem\CoreBundle\Entity\SwCgm;
use Urbem\CoreBundle\Entity\SwCgmPessoaFisica;
use Urbem\CoreBundle\Helper\UploadHelper;
use Urbem\CoreBundle\Resources\config\Sonata\AbstractSonataAdmin as AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Validator\Constraints as Assert;

class ClassificacaoInstitucionalAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'urbem_financeiro_orcamento_classificacao_institucional';
    protected $baseRoutePattern = 'financeiro/orcamento/classificacao-institucional';
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => "DESC",
        '_sort_by' => 'codEntidade'
    ];

    protected $includeJs = array(
        '/financeiro/javascripts/orcamento/classificacaoinstitucional/classificacaoinstituicional.js'
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'edit', 'create'));
        $collection->add('logotipo', '{_id}/logotipo', array('_controller' => 'FinanceiroBundle:Orcamento/ClassificacaoInstitucional:logotipo'), array('id' => $this->getRouterIdParameter()));
    }

    /**
     * @param string $context
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->where('o.exercicio = :exercicio');
        $query->setParameter('exercicio', $this->getExercicio());
        return $query;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $pager = $this->getDataGrid()->getPager();
        $pager->setCountColumn(array('codEntidade', 'exercicio'));

        $datagridMapper
            ->add('codEntidade', null, ['label' => 'label.codigo'])
            ->add('fkSwCgm.nomCgm', null, ['label' => 'label.nome'])
            ->add('exercicio', null, ['label' => 'label.exercicio'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setBreadCrumb();

        $listMapper
            ->add('codEntidade', 'text', ['label' => 'label.codigo'])
            ->add('fkSwCgm.nomCgm', 'text', ['label' => 'Nome'])
            ->add('exercicio')
        ;
        $this->addActionsGrid($listMapper);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $em = $this->getDoctrine();

        $id = $this->getAdminRequestId();
        $this->setBreadCrumb($id ? ['id' => $id] : []);

        $isEdit = false;
        /**
         * @var Entidade $entidade
         */
        $entidade = null;
        $usuariosEntidade = null;
        /**
         * @var Entidade $entidade
         */
        $repo = $em->getRepository(Entidade::class);

        if ($id) {
            $isEdit = true;

            /**
             * @var Entidade $entidade
             */
            $entidade = $this->getSubject();

            $usuariosEntidade = $em->getRepository(UsuarioEntidade::class)
                ->findBy([
                    'fkOrcamentoEntidade' => $entidade
                ]);
        }

        $entidadesList = $repo->getEntidadesParaCadastro();
        $entidadesCadastro = [];

        if (count($entidadesList)) {
            foreach ($entidadesList as $_entidade) {
                $entidadesCadastro[$_entidade['numcgm'] . ' - ' . trim($_entidade['nom_cgm'])] = $_entidade['numcgm'];
            }
        }

        $cgmsResponsaveisList = $repo->getResponsaveis();
        $cgmsResponsaveis = [];

        if (count($cgmsResponsaveisList)) {
            foreach ($cgmsResponsaveisList as $cgm) {
                $cgmsResponsaveis[$cgm['numcgm'] . ' - ' . trim($cgm['nom_cgm'])] = $cgm['numcgm'];
            }
        }

        $profissoes = $repo->getResponsaveisTecnicos();

        $profissoesList = [];

        if (count($profissoes)) {
            foreach ($profissoes as $profissao) {
                $profissoesList[$profissao['cod_profissao'] . ' - ' . trim($profissao['nom_profissao'])]
                    = $profissao['cod_profissao'];
            }
        }

        $usuariosDisponiveis = $repo->getUsuariosDisponiveis($this->getExercicio());
        $usuariosDisponiveisList = [];

        if (count($usuariosDisponiveis)) {
            foreach ($usuariosDisponiveis as $usuario) {
                $usuariosDisponiveisList[strtoupper(trim($usuario['nom_cgm']))] = $usuario['numcgm'];
            }
        }
        $usuariosEntidadeLista = [];
        if ($isEdit) {
            $usuariosEntidade = $entidade->getFkOrcamentoUsuarioEntidades();
            if (count($usuariosEntidade)) {
                $cgmRepo = $em->getRepository(SwCgm::class);
                foreach ($usuariosEntidade as $usuario) {
                    $usuarioCgm = $cgmRepo->find($usuario->getNumcgm());
                    $usuariosEntidadeLista[strtoupper(trim($usuarioCgm->getNumcgm() . ' - ' . trim($usuarioCgm->getNomCgm())))] = $usuarioCgm->getNumcgm();
                }
            }
        }

        $file = "";
        if ($this->id($this->getSubject())) {
            if (!empty($this->getSubject()->getFkOrcamentoEntidadeLogotipo())) {
                $file = '<a href="'.$this->generateUrl('logotipo', ['_id' => $this->getSubject()->getFkOrcamentoEntidadeLogotipo()->getLogotipo()]).
                    '" target="_blank" >'.$this->getSubject()->getFkOrcamentoEntidadeLogotipo()->getLogotipo(). '</a>'
                ;
            }
        }

        $formMapper
            ->with('label.orcamento.classificacaoInstitucional.dados')
            ->add(
                'numcgm',
                'choice',
                [
                    'choices' => $entidadesCadastro,
                    'attr' => [
                        'class' => 'select2-parameters '
                    ],
                    'placeholder' => 'label.selecione',
                    'label' => 'entidade',
                    'data' => $isEdit ? $entidade->getFkSwCgm()->getNumcgm() : null,
                ]
            )
            ->add(
                'codResponsavel',
                'choice',
                [
                    'label' => 'Responsável',
                    'mapped' => false,
                    'attr' => [
                        'class' => 'select2-parameters '
                    ],
                    'placeholder' => 'label.selecione',
                    'choices' => $cgmsResponsaveis,
                    'data' => $isEdit ? $entidade->getFkSwCgmPessoaFisica()->getNumcgm() : null,
                ]
            )
            ->add(
                'fkEconomicoResponsavelTecnico',
                'entity',
                [
                    'class' => ResponsavelTecnico::class,
                    'label' => 'label.responsavelTecnico',
                    'attr' => [
                        'class' => 'select2-parameters'
                    ],
                    'placeholder' => 'label.selecione'
                ],
                [
                    'admin_code' => 'core.admin.filter.economico.responsavel_tecnico',
                ]
            )
            ->add(
                'codProfissao',
                'hidden',
                [
                    'data' => !$isEdit ? null : $entidade->getCodRespTecnico()
                ]
            )
            ->add(
                'foto',
                'file',
                [
                    'mapped' => false,
                    'label' => 'label.logotipo',
                    'required' => false,
                    'help' => $file,
                    'constraints' => [
                        new Assert\File([
                            'mimeTypes' => ['image/jpeg', 'image/pjpeg'],
                            'mimeTypesMessage' => 'Somente arquivo JPEG'
                        ])
                    ]
                ]
            )
            ->add(
                'codUsuario',
                'choice',
                [
                    'mapped' => false,
                    'label'=> 'label.orcamentoInstitucional.usuarioDaEntidade',
                    'multiple' => true,
                    'data' => $usuariosEntidadeLista,
                    'choices' => $usuariosDisponiveisList,
                    'attr' => [
                        'class' => 'select2-parameters '
                    ],
                ],
                [
                    'placeholder' => 'label.selecione',
                ]
            )
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('exercicio')
            ->add('codRespTecnico')
            ->add('codProfissao')
            ->add('sequencia')
        ;
    }

    public function prePersist($object)
    {
        try {
            $this->saveEntidade();
        } catch (\Exception $e) {
            $this
                ->getFlashBag()
                ->add('error', $e->getMessage());

            return $this->redirectByRoute(
                $this->baseRouteName . '_create'
            );
        }
    }

    public function preUpdate($object)
    {
        $id = $this->getAdminRequestId();
        try {
            $this->saveEntidade();
            return $this->redirectByRoute(
                $this->baseRouteName.'_list'
            );
        } catch (\Exception $e) {
            $this
                ->getFlashBag()
                ->add('error', $e->getMessage());

            return $this->redirectByRoute(
                $this->baseRouteName . '_edit',
                ['id' => $id]
            );
        }
    }

    private function saveEntidade()
    {
        $em = $this->getDoctrine();
        $entidadeRepository = $em->getRepository(Entidade::class);

        $data = $this
            ->getRequest()
            ->request
            ->all();

        $formData = $data[$this->getUniqid()];

        /**
         * @var SwCgmPessoaFisica $swcgm
         */
        $swcgmResponsavel = $em
            ->getRepository(SwCgmPessoaFisica::class)
            ->find((int) $formData['codResponsavel']);

        /**
         * @var SwCgm $cgm
         */
        $swCgm = $em
            ->getRepository(SwCgm::class)
            ->find((int) $formData['numcgm']);

        $entidade = $this->getSubject();
        $findEntidade = $this->getDoctrine()->getRepository(Entidade::class)->findOneBy(['exercicio'=> $this->getExercicio(), 'codEntidade' => $entidade->getCodEntidade()]);
        if (empty($findEntidade)) {
            $entidade
                ->setFkSwCgm($swCgm)
                ->setFkSwCgmPessoaFisica($swcgmResponsavel)
                ->setCodProfissao((int) $formData['codProfissao'])
                ->setExercicio($this->getExercicio());

            if (null === $entidade->getCodEntidade()) {
                $entidade->setCodEntidade($entidadeRepository->getNextEntidadeCod());
            }
            $em->persist($entidade);
        } else {
            $entidade = $findEntidade;
        }

        // Salvar usuarios
        $this->saveUsuariosEntidade($formData, $entidade);

        // Upload fotos
        $foto = $this
            ->getForm()
            ->get('foto')
            ->getData();

        if ($foto) {
            $this->uploadFoto($foto, $formData, $entidade);
        }

        $em->flush();
        return $entidade;
    }

    private function uploadFoto($foto, $formData, $entidade)
    {
        if (!$foto) {
            return;
        }
        try {
            $upload = new UploadHelper();

            $uploadParameters = $this
                ->getContainer()
                ->getParameter('financeirobundle');

            $upload
                ->setPath($uploadParameters['institucionalPath'])
                ->setFile($foto)
            ;

            $fullFileName = $upload->executeUpload(
                (int) $formData['numcgm'] . uniqid()
            );

            if (!empty($entidade->getFkOrcamentoEntidadeLogotipo())) {
                if ($entidade->getExercicio() != $this->getExercicio()) {
                    $this->saveLogotipo($entidade, $fullFileName);
                } else {
                    $entidade->getFkOrcamentoEntidadeLogotipo()->setLogotipo($fullFileName['name']);
                }
            } else {
                $this->saveLogotipo($entidade, $fullFileName);
            }
        } catch (\Exception $e) {
            throw new \Exception($this->getContainer()->get('translator')->transChoice('label.orcamentoInstitucional.errorUploadFile', 0, [], 'messages'));
        }
    }

    /**
     * @param $entidade
     * @param $fullFileName
     */
    private function saveLogotipo($entidade, $fullFileName)
    {
        $entidadeLogotipo = new EntidadeLogotipo();

        $entidadeLogotipo
            ->setFkOrcamentoEntidade($entidade)
            ->setExercicio($this->getExercicio())
            ->setLogotipo($fullFileName['name']);
        $entidade->setFkOrcamentoEntidadeLogotipo($entidadeLogotipo);
    }


    private function saveUsuariosEntidade($formData, Entidade $entidade)
    {
        $em = $this->getDoctrine();

        $em->getRepository(Entidade::class)
            ->removeUsuariosEntidade(
                $entidade->getCodEntidade(),
                $entidade->getExercicio()
            );

        foreach ($formData['codUsuario'] as $codUsuario) {
            $usuario = new UsuarioEntidade();
            $usuario
                ->setExercicio($this->getExercicio())
                ->setCodEntidade($entidade->getCodEntidade())
                ->setNumcgm($codUsuario);

            $em->persist($usuario);
        }
        $em->flush();
    }
}
