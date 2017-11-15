<?php

namespace Urbem\CoreBundle\Repository\Tcemg;

use Doctrine\ORM\QueryBuilder;
use Urbem\CoreBundle\Repository\AbstractRepository;
use Urbem\PrestacaoContasBundle\Service\Tribunal\MG\Filter\ContratoFilter;

class ContratoRepository extends AbstractRepository
{
    public function getNextCodContrato()
    {
        return $this->nextVal('cod_contrato');
    }

    /**
     * @param $exercicio
     * @return int
     */
    public function getTotalByFilter(ContratoFilter $filter)
    {
        $qb = $this->getByFilterAsQueryBuilder($filter);
        $qb->select('COUNT(Contrato)');
        $qb->resetDQLPart('orderBy');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param ContratoFilter $filter
     * @param $limit
     * @param $offset
     * @return array
     */
    public function getByFilter(ContratoFilter $filter, $limit, $offset)
    {
        $qb = $this->getByFilterAsQueryBuilder($filter);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ContratoFilter $filter
     * @return QueryBuilder
     */
    public function getByFilterAsQueryBuilder(ContratoFilter $filter)
    {
        $qb = $this->createQueryBuilder('Contrato');

        $hasFilter = false;

        if (null !== $filter->getNroContrato()) {
            $hasFilter = true;

            $qb->andWhere('Contrato.nroContrato = :nroContrato');
            $qb->setParameter('nroContrato', $filter->getNroContrato());
        }

        if (null !== $filter->getDataPublicacao()) {
            $hasFilter = true;

            $qb->join('Contrato.dataPublicacao', 'dataPublicacao');
            $qb->setParameter('dataPublicacao', $filter->getDataPublicacao());
        }

        if (null !== $filter->getPeriodicidadeInicio()) {
            $hasFilter = true;

            $qb->andWhere('Contrato.dataInicio >= :periodicidadeInicio');
            $qb->setParameter('periodicidadeInicio', $filter->getPeriodicidadeInicio());
        }

        if (null !== $filter->getPeriodicidadeFim()) {
            $hasFilter = true;

            $qb->andWhere('Contrato.dataFinal <= :periodicidadeFim');
            $qb->setParameter('periodicidadeFim', $filter->getPeriodicidadeFim());
        }

        if (null !== $filter->getObjetoContrato()) {
            $hasFilter = true;

            $qb->andWhere($qb->expr()->like('Contrato.objetoContrato', $qb->expr()->literal(sprintf('%%%s%%', $filter->getObjetoContrato()))));
        }

        if (true === $hasFilter) {
            $qb->andWhere('Contrato.fkTcemgContratoRescisao IS EMPTY');
        }

        return $qb;
    }
}
