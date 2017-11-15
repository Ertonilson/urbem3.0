<?php

namespace Urbem\RecursosHumanosBundle\Resources\config\Sonata\FolhaPagamento;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Route\RouteCollection;
use Urbem\CoreBundle\Entity\Administracao\Gestao;
use Urbem\CoreBundle\Entity\Administracao\Modulo;
use Urbem\CoreBundle\Entity\Administracao\Relatorio;
use Urbem\CoreBundle\Entity\Folhapagamento\PeriodoMovimentacao;
use Urbem\CoreBundle\Entity\Folhapagamento\Previdencia;
use Urbem\CoreBundle\Entity\Folhapagamento\PrevidenciaRegimeRat;
use Urbem\CoreBundle\Entity\Orcamento\Entidade;
use Urbem\CoreBundle\Entity\Organograma\Local;
use Sonata\AdminBundle\Form\FormMapper;
use Urbem\CoreBundle\Model\Administracao\ConfiguracaoModel;
use Urbem\CoreBundle\Model\Folhapagamento\PeriodoMovimentacaoModel;
use Urbem\CoreBundle\Model\Folhapagamento\PrevidenciaModel;
use Urbem\CoreBundle\Resources\config\Sonata\Filter\Pessoal\GeneralFilterAdmin;

class ContribuicaoPrevidenciariaReportAdmin extends GeneralFilterAdmin
{
    protected $baseRouteName = 'urbem_recursos_humanos_folha_pagamento_relatorios_contribuicao_previdenciaria';
    protected $baseRoutePattern = 'recursos-humanos/folha-pagamento/relatorios/contribuicao-previdenciaria';
    protected $layoutDefaultReport = '/bundles/report/gestaoRH/fontes/RPT/folhaPagamento/report/design/contribuicaoPrevidenciaria.rptdesign';
    protected $legendButtonSave = ['icon' => 'receipt', 'text' => 'Gerar Relatório'];
    protected $includeJs = array('/recursoshumanos/javascripts/folhapagamento/contribuicaoPrevidenciaria.js');

    /**
     * @param RouteCollection $collection
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('create'));
    }

    public function prePersist($object)
    {
        $fileName = $this->parseNameFile("contribuicaoPrevidenciaria");

        /** @var EntityManager $em */
        $em = $this->modelManager->getEntityManager($this->getClass());

        /** @var ConfiguracaoModel $configuracaoModel */
        $configuracaoModel = new ConfiguracaoModel($em);
        $codEntidadePrefeitura = $configuracaoModel->getConfiguracao(
            'cod_entidade_prefeitura',
            Modulo::MODULO_ORCAMENTO,
            true,
            $this->getExercicio()
        );

        /** @var Entidade $entidade */
        $entidade = $em->getRepository(Entidade::class)->findOneBy(
            [
                'codEntidade' => $codEntidadePrefeitura,
                'exercicio' => $this->getExercicio()
            ]
        );

        $form = $this->getForm();
        $complementar = $form->get('inCodComplementar')->getData();
        $inCodConfiguracao = $form->get('tipoCalculo')->getData();
        $stOrdem = $form->get('ordenacao')->getData();
        $stTipoFiltro = $form->get('tipo')->getData();
        $contratos = $form->get('codContrato')->getData();
        $inCodLotacaoSelecionados = $form->get('lotacao')->getData();
        $inCodLocalSelecionados = $form->get('local')->getData();
        $stSituacao = $form->get('stSituacao')->getData();
        $previdencia = $form->get('previdencia')->getData();
        $boAgrupar = $form->get('boAgrupar')->getData();
        $boAgrupar['agrupar'] = (in_array('agrupar', $boAgrupar)) ? 1 : '';
        $boAgrupar['quebrar'] = (in_array('quebrar', $boAgrupar)) ? 1 : '';
        $boAcumularSalCompl = $form->get('boAcumularSalCompl')->getData();
        $stAcumularSalCompl = ($boAcumularSalCompl) ? 'sim' : 'nao';

        $inCodMes = ($form->get('mes')->getData() > 9) ? $form->get('mes')->getData() : "0" . $form->get('mes')->getData();

        $dtCompetencia = $inCodMes . "/" . $form->get('ano')->getData();

        /** @var PeriodoMovimentacao $periodoMovimentacao */
        $inCodPeriodoMovimentacao = $em->getRepository(PeriodoMovimentacao::class)
            ->consultaPeriodoMovimentacaoCompetencia($dtCompetencia);

        $stFiltro = " AND previdencia_regime_rat.cod_previdencia = ".$previdencia;

        /** @var PrevidenciaModel $previdenciaModel */
        $previdenciaModel = new PrevidenciaModel($em);
        $rsRat = $previdenciaModel->getPrevidenciaRat($stFiltro);

        $stFiltro = " WHERE cod_previdencia = ".$previdencia;
        $stFiltro.= " AND vigencia <= TO_DATE('".$inCodPeriodoMovimentacao['dt_final']."','DD/MM/YYYY')";
        $stFiltro.= " ORDER BY vigencia_ordenacao DESC, timestamp DESC LIMIT 1";

        $rsPrevidenciaPrevidencia = $previdenciaModel->getPrevidenciaPrevidencia($stFiltro);

        $stFiltroContratos = $stRegime = '';
        switch ($stTipoFiltro) {
            case "cgm_contrato":
                foreach ($contratos as $arContrato) {
                    $stFiltroContratos .= $arContrato->getCodContrato() . ",";
                }
                break;
            case "geral":
                break;
            case "lotacao":
                foreach ($inCodLotacaoSelecionados as $inCodOrgao) {
                    $stFiltroContratos .= $inCodOrgao . ",";
                }
                break;
            case "local":
                /** @var Local $inCodLocal */
                foreach ($inCodLocalSelecionados as $inCodLocal) {
                    $stFiltroContratos .= $inCodLocal->getCodLocal() . ",";
                }
                break;
            case "reg_sub_car_esp_grupo":
                $stFiltroContratos = implode(",", $form->get("regime")->getData()) . "#";
                $stFiltroContratos .= implode(",", $form->get("subdivisao")->getData()) . "#";
                $stFiltroContratos .= implode(",", $form->get("cargo")->getData()) . "#";
                if (is_array($form->get("especialidade")->getData())) {
                    $stFiltroContratos .= implode(",", $form->get("especialidade")->getData());
                }
                break;
            case "padrao":
                $stFiltroContratos .= implode(",", $form->get("padrao")->getData());
                break;
        }

        $params = [
            'term_user' => 'suporte',
            'cod_acao' => 1491,
            'exercicio' => $this->getExercicio(),
            'inCodGestao' => Gestao::GESTAO_RECURSOS_HUMANOS,
            'inCodModulo' => Modulo::MODULO_FOLHAPAGAMENTO,
            'inCodRelatorio' => Relatorio::RECURSOS_HUMANOS_FOLHAPAGAMENTO_CONTRIBUICAOPREVIDENCIARIA,
            'entidade' => $codEntidadePrefeitura,
            'stEntidade' => $entidade->getFkSwCgm()->getNomCgm(),
            'cod_periodo_movimentacao' =>  $inCodPeriodoMovimentacao['cod_periodo_movimentacao'],
            'cod_previdencia' => $previdencia,
            'cod_configuracao' => $inCodConfiguracao,
            'ordenacao' => $stOrdem,
            'stTipoFiltro' => $stTipoFiltro,
            'stRegime' => $stRegime,
            'stCodigos' => $stFiltroContratos,
            'periodo_inicial' => $inCodPeriodoMovimentacao['dt_inicial'],
            'periodo_final' => $inCodPeriodoMovimentacao['dt_final'],
            'aliquota_rat' => ($rsRat["aliquota_rat"] != '' ? number_format($rsRat["aliquota_rat"], 4, '.', '') : ''),
            'aliquota_fap' => ($rsRat["aliquota_fap"] != '' ? number_format($rsRat["aliquota_fap"], 4, '.', '') : ''),
            'aliquota_patronal' => $rsPrevidenciaPrevidencia['aliquota'],
            'stSituacaoCadastro' => $stSituacao,
            'stAcumularSalCompl' => $stAcumularSalCompl,
            'inCodComplementar' => (is_null($complementar)) ? 0 : $complementar
        ];

        $apiService = $this->getReportService();
        $apiService->setReportNameFile($fileName);
        $apiService->setLayoutDefaultReport($this->layoutDefaultReport);
        $res = $apiService->getReportContent($params);

        $this->parseContentToPdf(
            $res->getBody()->getContents(),
            $fileName
        );
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $id = $this->getAdminRequestId();
        $this->setBreadCrumb($id ? ['id' => $id] : []);

        /** @var EntityManager $entityManager */
        $entityManager = $this->modelManager->getEntityManager($this->getClass());
        /** @var PeriodoMovimentacaoModel $periodoMovimentacaoModel */
        $periodoMovimentacaoModel = new PeriodoMovimentacaoModel($entityManager);

        $fieldOptions = [];
        $fieldOptions['tipoCalculo'] = [
            'choices' => [
                'Complementar' => 0,
                'Salário' => 1,
                'Férias' => 2,
                '13o Salário' => 3,
                'Rescisao' => 4,
            ],
            'label' => 'Tipo de cálculo',
            'attr' => [
                'class' => 'select2-parameters '
            ],
            'mapped' => false,
            'data' => 1
        ];

        $fieldOptions['inCodComplementar'] = [
            'label' => 'label.recursosHumanos.relatorios.folha.contraCheque.inCodComplementar',
            'mapped' => false,
            'placeholder' => 'label.selecione',
            'choices' => [],
            'attr' => [
                'class' => 'select2-parameters',
                'disabled' => 'disabled'
            ],
        ];

        $fieldOptions['ano'] = [
            'label' => 'label.ferias.ano',
            'mapped' => false,
            'attr' => [
                'value' => $this->getExercicio(),
                'class' => 'numero '
            ],
        ];

        $mes = '';
        $fieldOptions['mes'] = [
            'label' => 'label.ferias.mes',
            'mapped' => false,
            'placeholder' => 'label.selecione',
            'choices' => $periodoMovimentacaoModel->getMesCompetenciaFolhaPagamento($this->getExercicio()),
            'attr' => [
                'data-mes' => $mes,
            ],
            'attr' => ['class' => 'select2-parameters '],
        ];

        $fieldOptions['stSituacao'] = [
            'choices' => [
                'Ativos' => 'ativos',
                'Rescindidos' => 'rescindidos',
                'Aposentados' => 'aposentados',
                'Pensionistas' => 'pensionistas',
                'Todos' => 'todos'
            ],
            'label' => 'label.recursosHumanos.relatorios.folha.customizavelEventos.stSituacao',
            'expanded' => false,
            'multiple' => false,
            'attr' => ['class' => 'select2-parameters'],
            'mapped' => false,
            'data' => 'ativos',
        ];

        $fieldOptions['ordenacao'] = [
            'choices' => [
                'Alfabética' => 'alfabetica',
                'Numérica' => 'numerica',
            ],
            'expanded' => true,
            'multiple' => false,
            'label_attr' => ['class' => 'checkbox-sonata '],
            'attr' => ['class' => 'checkbox-sonata '],
            'mapped' => false,
            'data' => 'alfabetica',
            'label' => 'label.recursosHumanos.relatorios.folha.contraCheque.ordenacao'
        ];

        $fieldOptions['boAgrupar'] = [
            'label' => 'label.recursosHumanos.relatorios.folha.customizavelEventos.boAgrupar',
            'mapped' => false,
            'choices' => [
                'Agrupar' => 'agrupar',
                'Quebrar Página' => 'quebrar',
            ],
            'data' => ['valor'],
            'expanded' => true,
            'multiple' => true,
            'label_attr' => ['class' => 'checkbox-sonata '],
            'attr' => ['class' => 'checkbox-sonata '],
            'required' => false
        ];

        /** @var PrevidenciaModel $previdenciaModel */
        $previdenciaModel = new PrevidenciaModel($entityManager);
        $previdencias = $previdenciaModel->getPrevidenciaChoices(true);
        $fieldOptions['previdencia'] = [
            'choices' => $previdencias,
            'mapped' => false,
            'label' => 'label.recursosHumanos.relatorios.folha.contribuicaoPrevidenciaria.previdencia',
            'attr' => ['class' => 'select2-parameters']
        ];

        $fieldOptions['boAcumularSalCompl'] = [
            'label' => 'label.recursosHumanos.relatorios.folha.contribuicaoPrevidenciaria.boAcumularSalCompl',
            'mapped' => false,
            'choices' => [
                'Sim' => 'sim',
                'Não' => 'nao',
            ],
            'data' => 'nao',
            'expanded' => true,
            'multiple' => false,
            'label_attr' => ['class' => 'checkbox-sonata '],
            'attr' => ['class' => 'checkbox-sonata '],
            'required' => true
        ];
        $formMapper
            ->with("Parâmetros para consulta")
            ->add('ano', 'number', $fieldOptions['ano'])
            ->add('mes', 'choice', $fieldOptions['mes'])
            ->add('tipoCalculo', 'choice', $fieldOptions['tipoCalculo'])
            ->add('inCodComplementar', 'choice', $fieldOptions['inCodComplementar'])
            ->add('boAcumularSalCompl', 'choice', $fieldOptions['boAcumularSalCompl'])
            ->end()
            ->with("Filtro");
        parent::configureFields($formMapper, GeneralFilterAdmin::RECURSOSHUMANOS_FOLHA_CONTRIBUICAOPREVIDENCIARIA);
        $formMapper
            ->add('boAgrupar', 'choice', $fieldOptions['boAgrupar'])
            ->add('previdencia', 'choice', $fieldOptions['previdencia'])
        ;
        $formMapper->end()
            ->with('')
            ->add('stSituacao', 'choice', $fieldOptions['stSituacao'])
            ->add('ordenacao', 'choice', $fieldOptions['ordenacao'])
            ->end();
    }
}
