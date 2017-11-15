<?php

namespace Urbem\CoreBundle\Repository\RecursosHumanos\Pessoal;

use Urbem\CoreBundle\Repository\AbstractRepository;

/**
 * Class ServidorRepository
 * @package Urbem\CoreBundle\Repository\RecursosHumanos\Pessoal
 */
class ServidorRepository extends AbstractRepository
{
    /**
     * @param $cgm
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function consultaDadosCgmPessoaFisica($cgm)
    {

        $qb = $this->createQueryBuilder('sw');
        $qb->leftJoin('Urbem\CoreBundle\Entity\SwPessoaFisica', 'p', 'WITH', 'sw.numcgm = p.numCgm');
        $qb->where('p.numCgm = :cgm');
        $qb->setParameter('cgm', $cgm);

        return $qb;
    }

    /**
     * @param $codServidor
     * @param $codCtps
     * @return array|string
     */
    public function inserirServidorCtps($codServidor, $codCtps)
    {
        $result = "";
        $this->consultaServidorCtps($codServidor);
        foreach ($codCtps->getValues() as $chave) {
            $val = $chave->getCodCtps();
            $sql = "INSERT INTO pessoal.servidor_ctps(cod_servidor,cod_ctps) VALUES($codServidor,$val);";

            $query = $this->_em->getConnection()->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(\PDO::FETCH_OBJ);
        }

        return $result;
    }

    /**
     * @param $codServidor
     * @return array
     */
    public function consultaServidorCtps($codServidor)
    {
        $sql = "
        SELECT
            *
        FROM pessoal.servidor_ctps
        WHERE cod_servidor = $codServidor";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_OBJ);

        if ($result > 0) {
            $this->deleteServidorCtps($codServidor);
        }

        return $result;
    }

    /**
     * @param $codServidor
     * @return array
     */
    public function deleteServidorCtps($codServidor)
    {
        $sql = "
        DELETE
        FROM pessoal.servidor_ctps
        WHERE cod_servidor = $codServidor";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_OBJ);

        return $result;
    }

    /**
     * @return int
     */
    public function getNextCodServidor()
    {
        return $this->nextVal('cod_servidor');
    }
}
