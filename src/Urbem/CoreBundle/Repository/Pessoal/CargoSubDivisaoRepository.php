<?php

namespace Urbem\CoreBundle\Repository\Pessoal;

use Doctrine\ORM;

/**
 * Class CargoSubDivisaoRepository
 * @package Urbem\CoreBundle\Repository\Pessoal
 */
class CargoSubDivisaoRepository extends ORM\EntityRepository
{
    /**
     * @param $info
     * @param $codCargo
     * @return array
     */
    public function getCargoSubDivisaoPorTimestamp($info, $codCargo)
    {
        $sql = "
        SELECT
            cargo_sub_divisao.cod_cargo,
            cargo_sub_divisao.cod_sub_divisao,
            cargo_sub_divisao.nro_vaga_criada,
            sub_divisao.cod_regime
        FROM
            pessoal.cargo_sub_divisao
        INNER JOIN
            pessoal.sub_divisao on sub_divisao.cod_sub_divisao = cargo_sub_divisao.cod_sub_divisao
        WHERE
            cod_cargo = ". $codCargo ."
        AND
            date_trunc('second', \"timestamp\") = '". $info->format('Y-m-d H:i:s') ."'
        ";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_OBJ);
        return $result;
    }

    /**
     * @param integer $inCodRegime
     * @param integer $inCodSubDivisao
     * @param integer $inCodCargo
     * @param integer $inCodPeriodoMovimentacao
     * @param boolean $boLiberaVagaMesRescisao
     * @param string $stEntidade
     * @return integer
     */
    public function getVagasOcupadasCargo(
        $inCodRegime,
        $inCodSubDivisao,
        $inCodCargo,
        $inCodPeriodoMovimentacao = 0,
        $boLiberaVagaMesRescisao = true,
        $stEntidade = ''
    ) {
        $sql = <<<SQL
SELECT
    getVagasOcupadasCargo (:inCodRegime,
        :inCodSubDivisao,
        :inCodCargo,
        :inCodPeriodoMovimentacao,
        :boLiberaVagaMesRescisao,
        :stEntidade) AS vagas
SQL;

        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue('inCodRegime', $inCodRegime);
        $query->bindValue('inCodSubDivisao', $inCodSubDivisao);
        $query->bindValue('inCodCargo', $inCodCargo);
        $query->bindValue('inCodPeriodoMovimentacao', $inCodPeriodoMovimentacao);
        $query->bindValue('boLiberaVagaMesRescisao', $boLiberaVagaMesRescisao);
        $query->bindValue('stEntidade', $stEntidade);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_OBJ);

        return $result->vagas;
    }
}
