<?php

namespace Urbem\CoreBundle\Repository\Administracao;

use Urbem\CoreBundle\Repository\AbstractRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AcaoRepository
 * @package Urbem\CoreBundle\Repository\Administracao
 */
class AcaoRepository extends AbstractRepository
{
    /**
     * @param array $filters
     * @return array
     */
    public function findAcao(array $filters = [])
    {

        $qb = $this->createQueryBuilder('a');

        $this->addFilterQueryBuilder($qb, $filters);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     * @return QueryBuilder
     */
    protected function addFilterQueryBuilder(QueryBuilder $qb, array $filters)
    {
        foreach ($filters as $column => $value) {
            if (is_null($value) or empty($value)) {
                continue;
            }
            $qb->andWhere('a.' . $column . ' = :' . $column)->setParameter($column, $value);
        }

            return $qb;
    }
}
