<?php

namespace Urbem\CoreBundle\Repository\Imobiliario;

use Doctrine\ORM;

class LoteRepository extends ORM\EntityRepository
{
    /**
     * @param $codLote
     * @return array
     */
    public function recuperaProprietariosLote($codLote)
    {
        $sql = "select p.numcgm from imobiliario.fn_recupera_lote_proprietarios(:codLote) as p(numcgm integer)";
        
        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue('codLote', $codLote, \PDO::PARAM_INT);

        $query->execute();
        return $query->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
}
