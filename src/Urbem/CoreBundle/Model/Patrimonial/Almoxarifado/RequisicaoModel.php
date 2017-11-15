<?php

namespace Urbem\CoreBundle\Model\Patrimonial\Almoxarifado;

use Doctrine\ORM;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Urbem\CoreBundle\AbstractModel;
use Urbem\CoreBundle\Entity\Almoxarifado\Requisicao;
use Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoHomologada;
use Urbem\CoreBundle\Repository\Patrimonio\Almoxarifado\RequisicaoRepository;

/**
 * Class RequisicaoModel
 */
class RequisicaoModel extends AbstractModel
{
    protected $entityManager = null;

    /** @var RequisicaoRepository|null $repository */
    protected $repository = null;

    /**
     * RequisicaoModel constructor.
     * @param ORM\EntityManager $entityManager
     */
    public function __construct(ORM\EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Requisicao::class);
    }

    /**
     * @param Requisicao $requisicao
     * @return bool
     */
    public function canRemove(Requisicao $requisicao)
    {
        $requisicaoHomologada = $requisicao->getFkAlmoxarifadoRequisicaoHomologadas()->filter(
            function (RequisicaoHomologada $requisicaoHomologada) {
                if (true == $requisicaoHomologada->getHomologada()) {
                    return $requisicaoHomologada;
                }
            }
        );

        $canRemove = $requisicaoHomologada->isEmpty();

        if (true == $canRemove) {
            (new RequisicaoItemModel($this->entityManager))->removeAll($requisicao);
            (new RequisicaoHomologadaModel($this->entityManager))->removeAll($requisicao);
        }

        return $canRemove;
    }

    /**
     * @param Requisicao $requisicao
     */
    public function removeRequisicaoDependencies(Requisicao $requisicao)
    {

    }

    /**
     * @param string $exercicio
     * @param int $codAlmoxarifado
     * @return int
     */
    public function buildCodRequisicao($exercicio, $codAlmoxarifado)
    {
        return $this->repository->getNextCodRequisicao($exercicio, $codAlmoxarifado);
    }

    /**
     * Retorna um array de requisicoes sem devoluçoes
     *
     * @return array|null
     */
    public function getRequisicoesSemDevolucao()
    {
        return $this->repository->getTodasRequisicoesSemDevolucao();
    }

    /**
     * Retorna um array de requisicoes para efetuar saida
     *
     * @return array|null
     */
    public function getRequisicoesParaDevolucao()
    {
        return $this->repository->getTodasRequisicoesParaDevolucao();
    }

    /**
     * @param ProxyQuery $proxyQuery
     * @param $tipo
     * @return ProxyQuery
     */
    public function getRequisicoes(ProxyQuery $proxyQuery, $tipo)
    {
        $tipo = strtolower(substr($tipo, 0, 1));

        $results = [];
        switch ($tipo) {
            case "s":
                $results = $this->getRequisicoesParaDevolucao();
                break;
            case "e":
                $results = $this->getRequisicoesSemDevolucao();
                break;
        }

        $alias = $proxyQuery->getRootAlias();

        $ids = [];
        foreach ($results as $requisicaoId) {
            $ids[] = $requisicaoId['cod_requisicao'];
        }

        $requisicoes = (empty($ids) ? 0 : $ids);

        $proxyQuery
            ->andWhere(
                $proxyQuery->expr()->in("{$alias}.codRequisicao", $requisicoes)
            )
        ;

        return $proxyQuery;
    }

    /**
     * Valida se a Requisicao enviada por param esta disponivel para Homologaçao
     *
     * @param Requisicao $requisicao
     * @return bool
     */
    public function isPassivelHomologacao(Requisicao $requisicao)
    {
        $results = $this->repository->getIfRequisicaoPassivelHomologacao(
            $requisicao->getExercicio(),
            $requisicao->getCodRequisicao(),
            $requisicao->getCodAlmoxarifado()
        );

        $isPassivelHomologacao = count($results) > 0 ? true : false;

        return $isPassivelHomologacao;
    }
}
