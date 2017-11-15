<?php

namespace Urbem\CoreBundle\Repository\Orcamento;

use Doctrine\ORM;
use Urbem\CoreBundle\Helper\ArrayHelper;

class DespesaRepository extends ORM\EntityRepository
{
    public function getPAO($exercicio)
    {
        $query = $this->_em->getConnection()->prepare(
            sprintf(
                "SELECT sw_fn_mascara_dinamica('99.99.99.999.9999.9999', despesa.num_orgao||'.'||despesa.num_unidade||'.'||despesa.cod_funcao||'.'||despesa.cod_subfuncao||'.'||p_programa.num_programa||'.'||acao.num_acao) AS dotacao
                     , pao.exercicio
                     , pao.num_pao
                     , acao.num_acao
                     , acao_dados.titulo
                FROM orcamento.despesa
                INNER JOIN orcamento.programa
                        ON programa.exercicio = despesa.exercicio
                       AND programa.cod_programa = despesa.cod_programa
                INNER JOIN orcamento.programa_ppa_programa
                        ON programa_ppa_programa.exercicio = programa.exercicio
                       AND programa_ppa_programa.cod_programa = programa.cod_programa
                INNER JOIN ppa.programa AS p_programa
                        ON p_programa.cod_programa = programa_ppa_programa.cod_programa_ppa
                INNER JOIN orcamento.pao
                        ON pao.exercicio = despesa.exercicio
                       AND pao.num_pao  = despesa.num_pao
                INNER JOIN orcamento.pao_ppa_acao
                        ON pao_ppa_acao.exercicio = pao.exercicio
                       AND pao_ppa_acao.num_pao = pao.num_pao
                INNER JOIN ppa.acao
                        ON acao.cod_acao = pao_ppa_acao.cod_acao
                INNER JOIN ppa.acao_dados
                        ON acao_dados.cod_acao = acao.cod_acao
                       AND acao_dados.timestamp_acao_dados = acao.ultimo_timestamp_acao_dados
                WHERE pao.exercicio = '%s'
                GROUP BY dotacao
                     , pao.exercicio
                     , pao.num_pao
                     , acao.num_acao
                     , acao_dados.titulo
                ORDER BY acao.num_acao, dotacao",
                $exercicio
            )
        );

        $query->execute();
        return $query->fetchAll();
    }

    public function recuperaSaldoDotacao($exercicio, $cod_despesa)
    {
        $query = $this->_em->getConnection()->prepare(
            sprintf("SELECT empenho.fn_saldo_dotacao ('".$exercicio."',".$cod_despesa.") AS saldo_dotacao;")
        );

        $query->execute();
        return $query->fetchAll();
    }

    /**
     * @param string|int $exercicio
     * @param int        $codDespesa
     * @param int        $codEntidade
     * @param string     $dataEmpenho
     * @param string     $tipoEmissao
     *
     * @return array
     */
    public function recuperaSaldoDotacaoDataEmpenho($exercicio, $codDespesa, $codEntidade, $dataEmpenho, $tipoEmissao)
    {
        $sql = "SELECT empenho.fn_saldo_dotacao_data_empenho(:exercicio, :cod_despesa, :data_empenho, :cod_entidade, :tipo_emissao) AS saldo_anterior;";

        $conn = $this->_em->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'exercicio'    => $exercicio,
            'cod_despesa'  => $codDespesa,
            'data_empenho' => $dataEmpenho,
            'cod_entidade' => $codEntidade,
            'tipo_emissao' => $tipoEmissao,
        ]);

        return $stmt->fetch();
    }

    public function recuperaCodEstrutural($exercicio, $cod_despesa)
    {
        $sql = "
        SELECT                                                   
            conta_despesa.cod_estrutural
            , conta_despesa.descricao
            , conta_despesa.cod_conta
            , conta_despesa.exercicio
        FROM
          orcamento.conta_despesa
        WHERE
	      EXISTS ( SELECT 1 
	      FROM orcamento.despesa
	      WHERE cod_despesa = :cod_despesa
	      AND despesa.cod_conta = conta_despesa.cod_conta)
	      AND conta_despesa.exercicio = :exercicio
	      ORDER BY conta_despesa.descricao 
        ";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue("cod_despesa", $cod_despesa, \PDO::PARAM_STR);
        $query->bindValue("exercicio", $exercicio, \PDO::PARAM_STR);
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_OBJ);
    }


    public function recuperaCodEstruturalUnico($exercicio, $cod_item)
    {
        $sql = "
        SELECT
          CD.cod_estrutural as cod_estrutural
          ,Solic.cod_despesa
        FROM
          orcamento.conta_despesa  AS CD,
          compras.solicitacao_item_dotacao AS Solic
        WHERE
          Solic.cod_conta     = CD.cod_conta
          AND Solic.exercicio     = CD.exercicio
          AND solic.exercicio = :exercicio AND Solic.cod_item = :cod_item  
          
        ";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue("cod_item", $cod_item, \PDO::PARAM_STR);
        $query->bindValue("exercicio", $exercicio, \PDO::PARAM_STR);
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_OBJ);
    }


    public function findDespesaByExercicio($exercicio)
    {
        $query = $this->_em->getConnection()->prepare(
            sprintf("SELECT d.cod_despesa, c.descricao FROM orcamento.despesa d INNER JOIN orcamento.conta_despesa c ON d.exercicio = c.exercicio and d.cod_conta = c.cod_conta WHERE d.exercicio = '".$exercicio."' ORDER BY d.cod_despesa;")
        );
        $query->execute();
        $despesas = $query->fetchAll();

        $options = [];

        foreach ($despesas as $despesa) {
            $options[$despesa['cod_despesa'] .' - ' . $despesa['descricao']] = $despesa['cod_despesa'];
        }
        return $options;
    }

    public function findDespesaByEntidade($exercicio, $entidades)
    {
        $query = $this->_em->getConnection()->prepare(
            sprintf("SELECT d.cod_despesa FROM orcamento.despesa d INNER JOIN orcamento.conta_despesa c ON d.exercicio = c.exercicio and d.cod_conta = c.cod_conta WHERE d.exercicio = '".$exercicio."' AND d.cod_entidade IN (".implode(',', $entidades).") ORDER BY d.cod_despesa;")
        );
        $query->execute();
        $despesas = $query->fetchAll();
        return ArrayHelper::parseArrayToChoice($despesas, 'cod_despesa', 'cod_despesa');
    }

    public function findDespesaByRecurso($exercicio, $codRecurso)
    {
        $query = $this->_em->getConnection()->prepare(
            sprintf("SELECT d.cod_despesa FROM orcamento.despesa d INNER JOIN orcamento.conta_despesa c ON d.exercicio = c.exercicio and d.cod_conta = c.cod_conta WHERE d.exercicio = '".$exercicio."' AND d.cod_recurso = ".$codRecurso." ORDER BY d.cod_despesa;")
        );
        $query->execute();
        $despesas = $query->fetchAll();
        return ArrayHelper::parseArrayToChoice($despesas, 'cod_despesa', 'cod_despesa');
    }

    public function getDespesaLiquidacaoEmpenho($exercicio, $codDespesa)
    {
        $sql = sprintf(
            "select
                    CD.mascara_classificacao,
                    ppa.acao.num_acao as num_acao,
                    trim( CD.descricao ) as descricao,
                    OD.*,
                    R.cod_recurso,
                    R.nom_recurso,
                    R.cod_fonte,
                    publico.fn_mascara_dinamica(
                        (
                            select
                                valor
                            from
                                administracao.configuracao
                            where
                                parametro = 'masc_despesa'
                                and exercicio = '2013'
                        ),
                        OD.num_orgao || '.' || OD.num_unidade || '.' || OD.cod_funcao || '.' || OD.cod_subfuncao || '.' || ppa.programa.num_programa || '.' || ppa.acao.num_acao || '.' || replace(
                            cd.mascara_classificacao,
                            '.',
                            ''
                        )
                    )|| '.' || replace(
                        r.cod_fonte,
                        '.',
                        ''
                    ) as dotacao
                from
                    orcamento.vw_classificacao_despesa as CD,
                    orcamento.despesa as OD join orcamento.programa_ppa_programa on
                    programa_ppa_programa.cod_programa = OD.cod_programa
                    and programa_ppa_programa.exercicio = OD.exercicio join ppa.programa on
                    ppa.programa.cod_programa = programa_ppa_programa.cod_programa_ppa join orcamento.pao_ppa_acao on
                    pao_ppa_acao.num_pao = OD.num_pao
                    and pao_ppa_acao.exercicio = OD.exercicio join ppa.acao on
                    ppa.acao.cod_acao = pao_ppa_acao.cod_acao,
                    orcamento.recurso as R
                where
                    CD.exercicio is not null
                    and OD.cod_conta = CD.cod_conta
                    and OD.exercicio = CD.exercicio
                    and OD.cod_recurso = R.cod_recurso
                    and OD.exercicio = R.exercicio
                    and CD.exercicio = '%s'
                    and OD.cod_despesa = %d",
            $exercicio,
            $codDespesa
        );

        $query = $this->_em->getConnection()->prepare($sql);

        $query->execute();
        $result = $query->fetchAll();
        return array_shift($result);
    }
    
    public function getDotacaoFormatada($dotacao, $exercicio)
    {
        $sql = "
        SELECT
            publico.fn_mascara_dinamica (
                (
                    SELECT
                        valor
                    FROM
                        administracao.configuracao
                    WHERE
                        parametro = 'masc_despesa'
                        AND exercicio = :exercicio ),
                :dotacao ) AS dotacao_formatada;
        ";
        
        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue("exercicio", $exercicio, \PDO::PARAM_STR);
        $query->bindValue("dotacao", $dotacao, \PDO::PARAM_STR);
        $query->execute();
        
        return $query->fetch(\PDO::FETCH_OBJ);
    }
}
