<?php

namespace Urbem\CoreBundle\Repository\Orcamento;

use Doctrine\ORM;

class RecursoRepository extends ORM\EntityRepository
{
    public function findRecurso(array $paramsWhere, $paramsExtra = null)
    {
        $sql = sprintf(
            "SELECT * FROM orcamento.recurso WHERE %s",
            implode(" AND ", $paramsWhere)
        );
        $sql .= $paramsExtra ? " ".$paramsExtra : "";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * @param $exercicio
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function withExercicioQueryBuilder($exercicio)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where('r.exercicio = :exercicio');
        $qb->setParameter('exercicio', $exercicio);
        $qb->orderBy('r.exercicio', 'ASC');
        $qb->addOrderBy('r.codRecurso', 'ASC');

        return $qb;
    }
}
