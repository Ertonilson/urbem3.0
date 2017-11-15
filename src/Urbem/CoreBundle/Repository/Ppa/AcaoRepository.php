<?php

namespace Urbem\CoreBundle\Repository\Ppa;

use Urbem\CoreBundle\Repository\AbstractRepository;

class AcaoRepository extends AbstractRepository
{
    public function verificaAcaoExistente($numAcao, $codPpa)
    {
        $query = $this->_em->getConnection()->prepare(
            sprintf(
                "SELECT *
	              FROM (
	                SELECT LPAD(acao.num_acao::VARCHAR,4,'0') AS num_acao
	                     , LPAD(acao.cod_acao::VARCHAR,4,'0') AS cod_acao
	                     , acao_dados.descricao
	                     , acao_dados.titulo
	                     , programa.num_programa
	                     , programa_dados.identificacao
	                     , programa_dados.objetivo
	                     , programa_dados.diagnostico
	                     , programa_dados.diretriz
	                     , programa_dados.publico_alvo
	                     , programa_dados.continuo
	                     , to_real(SUM(acao_recurso.valor)) AS valor
	                     , acao.ultimo_timestamp_acao_dados
	                     , ppa.cod_ppa
	                     , acao_dados.cod_funcao
	                     , acao_dados.cod_subfuncao
	                     , funcao.descricao AS desc_funcao
	                     , subfuncao.descricao AS desc_subfuncao
	                     , acao_dados.cod_tipo
	                     , tipo_acao.descricao as desc_tipo
	                     , '' AS exercicio
	                  FROM ppa.acao
	            INNER JOIN ppa.acao_dados
	                    ON acao.cod_acao = acao_dados.cod_acao
	                   AND acao.ultimo_timestamp_acao_dados = acao_dados.timestamp_acao_dados
	            INNER JOIN ppa.tipo_acao
	                    ON acao_dados.cod_tipo = tipo_acao.cod_tipo
	             LEFT JOIN orcamento.funcao
	                    ON acao_dados.exercicio = funcao.exercicio
	                   AND acao_dados.cod_funcao = funcao.cod_funcao
	             LEFT JOIN orcamento.subfuncao
	                    ON acao_dados.exercicio = subfuncao.exercicio
	                   AND acao_dados.cod_subfuncao = subfuncao.cod_subfuncao
	             LEFT JOIN ppa.acao_recurso
	                    ON acao.cod_acao = acao_recurso.cod_acao
	                   AND acao.ultimo_timestamp_acao_dados = acao_recurso.timestamp_acao_dados
	            INNER JOIN ppa.programa
	                    ON acao.cod_programa = programa.cod_programa
	            INNER JOIN ppa.programa_dados
	                    ON programa_dados.cod_programa = programa.cod_programa
	                   AND programa_dados.timestamp_programa_dados = programa.ultimo_timestamp_programa_dados
	            INNER JOIN ppa.programa_setorial
	                    ON programa.cod_setorial = programa_setorial.cod_setorial
	            INNER JOIN ppa.macro_objetivo
	                    ON macro_objetivo.cod_macro = programa_setorial.cod_macro
	            INNER JOIN ppa.ppa
	                    ON macro_objetivo.cod_ppa = ppa.cod_ppa
	        GROUP BY acao.num_acao
	                          , acao.cod_acao
	                          , acao_dados.descricao
	                          , acao_dados.titulo
	                          , programa.num_programa
	                          , programa_dados.identificacao
	                          , programa_dados.objetivo
	                          , programa_dados.diagnostico
	                          , programa_dados.diretriz
	                          , programa_dados.publico_alvo
	                          , programa_dados.continuo
	                          , acao.ultimo_timestamp_acao_dados
	                          , ppa.cod_ppa
	                          , acao_dados.cod_funcao
	                          , acao_dados.cod_subfuncao
	                          , funcao.descricao
	                          , subfuncao.descricao
	                          , acao_dados.cod_tipo
	                          , tipo_acao.descricao
	                      UNION

	                SELECT LPAD(pao.num_pao::VARCHAR,4,'0') AS num_acao
	                     , LPAD(pao.num_pao::VARCHAR,4,'0') AS cod_acao
	                     , pao.nom_pao AS descricao
	                     , pao.nom_pao AS titulo
	                     , null AS num_programa
	                     , '' AS identificacao
	                     , '' AS objetivo
	                     , '' AS diagnostico
	                     , '' AS diretriz
	                     , '' AS publico_alvo
	                     , null AS continuo
	                     , TO_REAL(0) AS valor
	                     , null AS ultimo_timestamp_acao_dados
	                     , null AS cod_ppa
	                     , null AS cod_funcao
	                     , null AS cod_subfuncao
	                     , '' AS desc_funcao
	                     , '' AS desc_subfuncao
	                     , (SELECT orcamento.fn_consulta_tipo_pao(pao.exercicio,pao.num_pao)) AS cod_tipo
	                     , CASE WHEN ( (SELECT orcamento.fn_consulta_tipo_pao(pao.exercicio,pao.num_pao)) = 1 )
	                            THEN 'Projeto'                                                                                                               WHEN ( (SELECT orcamento.fn_consulta_tipo_pao(pao.exercicio,pao.num_pao)) = 2 )
	                            THEN 'Atividade'
	                            WHEN ( (SELECT orcamento.fn_consulta_tipo_pao(pao.exercicio,pao.num_pao)) = 3 )
	                            THEN 'Operações Especiais'
	                            WHEN ( (SELECT orcamento.fn_consulta_tipo_pao(pao.exercicio,pao.num_pao)) = 4 )
	                            THEN 'Não Orçamentária'
	                       END AS desc_tipo
	                     , pao.exercicio
	                  FROM orcamento.pao
	                 INNER JOIN ( SELECT num_pao
	                                   , MAX(exercicio) AS exercicio
	                                FROM orcamento.pao
	                            GROUP BY num_pao
	                            ) AS max_pao
	                         ON max_pao.num_pao   = pao.num_pao
	                        AND max_pao.exercicio = pao.exercicio
	                      WHERE NOT EXISTS ( SELECT 1
	                                           FROM orcamento.pao_ppa_acao
	                                          WHERE pao.exercicio = pao_ppa_acao.exercicio
	                                            AND pao.num_pao   = pao_ppa_acao.num_pao)
	                    ) AS tabela  WHERE  num_acao::INTEGER = :numAcao AND cod_ppa = :codPpa"
            )
        );
        $query->bindValue('numAcao', $numAcao);
        $query->bindValue('codPpa', $codPpa);
        $query->execute();
        return $query->fetchAll();
    }

    public function verificaMetasFisicasRealizadas($ano, $exercicio)
    {
        $query = $this->_em->getConnection()->prepare(
            sprintf(
                "SELECT  ano1.cod_acao
                       , LPAD(acao.num_acao::TEXT, 4, '0') AS num_acao
                       , ano1.timestamp_acao_dados
                       , recurso.masc_recurso AS cod_recurso_mascarado
                       , ano1.cod_recurso
                       , recurso.nom_recurso
                       , recurso.masc_recurso||' - '||recurso.nom_recurso AS nom_cod_recurso
                       , ano1.exercicio_recurso AS ano1
                       , COALESCE(qtd_ano1.quantidade, 0.00) AS ano1_qtd
                       , COALESCE(ano1.valor, 0.00) AS ano1_valor
                       , COALESCE(realizada_1.valor, 0.00) AS ano1_realizada
                       , realizada_1.justificativa AS ano1_justificativa
                       , ano2.exercicio_recurso AS ano2
                       , COALESCE(qtd_ano2.quantidade, 0.00) AS ano2_qtd
                       , COALESCE(ano2.valor, 0.00) AS ano2_valor
                       , COALESCE(realizada_2.valor, 0.00) AS ano2_realizada
                       , realizada_2.justificativa AS ano2_justificativa
                       , ano3.exercicio_recurso AS ano3
                       , COALESCE(qtd_ano3.quantidade, 0.00) AS ano3_qtd
                       , COALESCE(ano3.valor, 0.00) AS ano3_valor
                       , COALESCE(realizada_3.valor, 0.00) AS ano3_realizada
                       , realizada_3.justificativa AS ano3_justificativa
                       , ano4.exercicio_recurso AS ano4
                       , COALESCE(qtd_ano4.quantidade, 0.00) AS ano4_qtd
                       , COALESCE(ano4.valor, 0.00) AS ano4_valor
                       , COALESCE(realizada_4.valor, 0.00) AS ano4_realizada
                       , realizada_4.justificativa AS ano4_justificativa
                       , COALESCE(qtd_ano1.quantidade, 0.00) + COALESCE(qtd_ano2.quantidade, 0.00) + COALESCE(qtd_ano3.quantidade, 0.00) + COALESCE(qtd_ano4.quantidade, 0.00) as total_qtd
                       , COALESCE(ano1.valor, 0.00) + COALESCE(ano2.valor, 0.00) + COALESCE(ano3.valor, 0.00) + COALESCE(ano4.valor, 0.00) as total_valor
                       , ppa.cod_ppa
                       , ppa.cod_ppa||' - '||ppa.ano_inicio||' a '||ppa.ano_final AS nom_ppa
                  FROM ppa.acao_recurso AS ano1
                  INNER JOIN orcamento.recurso(:exercicio) AS recurso
                          ON ano1.cod_recurso   = recurso.cod_recurso
                   LEFT JOIN ppa.acao_recurso AS ano2
                          ON ano2.ano = '1'
                         AND ano1.cod_acao             = ano2.cod_acao
                         AND ano1.timestamp_acao_dados = ano2.timestamp_acao_dados
                         AND ano1.cod_recurso          = ano2.cod_recurso
                   LEFT JOIN ppa.acao_recurso AS ano3
                          ON ano3.ano = '2'
                         AND ano1.cod_acao             = ano3.cod_acao
                         AND ano1.timestamp_acao_dados = ano3.timestamp_acao_dados
                         AND ano1.cod_recurso          = ano3.cod_recurso
                   LEFT JOIN ppa.acao_recurso AS ano4
                          ON ano4.ano = '4'
                         AND ano1.cod_acao             = ano4.cod_acao
                         AND ano1.timestamp_acao_dados = ano4.timestamp_acao_dados
                         AND ano1.cod_recurso          = ano4.cod_recurso
                   LEFT JOIN ppa.acao_quantidade as qtd_ano1
                          ON qtd_ano1.ano                  = ano1.ano
                         AND qtd_ano1.cod_acao             = ano1.cod_acao
                         AND qtd_ano1.timestamp_acao_dados = ano1.timestamp_acao_dados
                         AND qtd_ano1.cod_recurso          = ano1.cod_recurso
                   LEFT JOIN ppa.acao_quantidade as qtd_ano2
                          ON qtd_ano2.ano                  = ano2.ano
                         AND qtd_ano2.cod_acao             = ano2.cod_acao
                         AND qtd_ano2.timestamp_acao_dados = ano2.timestamp_acao_dados
                         AND qtd_ano2.cod_recurso          = ano2.cod_recurso
                   LEFT JOIN ppa.acao_quantidade as qtd_ano3
                          ON qtd_ano3.ano                  = ano3.ano
                         AND qtd_ano3.cod_acao             = ano3.cod_acao
                         AND qtd_ano3.timestamp_acao_dados = ano3.timestamp_acao_dados
                         AND qtd_ano3.cod_recurso          = ano3.cod_recurso
                   LEFT JOIN ppa.acao_quantidade as qtd_ano4
                          ON qtd_ano4.ano                  = ano4.ano
                         AND qtd_ano4.cod_acao             = ano4.cod_acao
                         AND qtd_ano4.timestamp_acao_dados = ano4.timestamp_acao_dados
                         AND qtd_ano4.cod_recurso          = ano4.cod_recurso

                  INNER JOIN ppa.acao
                          ON acao.cod_acao = ano1.cod_acao
                         AND acao.ultimo_timestamp_acao_dados = ano1.timestamp_acao_dados
                  INNER JOIN ppa.acao_dados
                          ON acao.cod_acao = acao_dados.cod_acao
                         AND acao.ultimo_timestamp_acao_dados = acao_dados.timestamp_acao_dados
                  INNER JOIN ppa.tipo_acao
                          ON acao_dados.cod_tipo = tipo_acao.cod_tipo
                   LEFT JOIN orcamento.funcao
                          ON acao_dados.exercicio = funcao.exercicio
                         AND acao_dados.cod_funcao = funcao.cod_funcao
                   LEFT JOIN orcamento.subfuncao
                          ON acao_dados.exercicio = subfuncao.exercicio
                         AND acao_dados.cod_subfuncao = subfuncao.cod_subfuncao
                   LEFT JOIN ppa.acao_recurso
                          ON acao.cod_acao = acao_recurso.cod_acao
                         AND acao.ultimo_timestamp_acao_dados = acao_recurso.timestamp_acao_dados
                  INNER JOIN ppa.programa
                          ON acao.cod_programa = programa.cod_programa
                  INNER JOIN ppa.programa_dados
                          ON programa_dados.cod_programa = programa.cod_programa
                         AND programa_dados.timestamp_programa_dados = programa.ultimo_timestamp_programa_dados
                  INNER JOIN ppa.programa_setorial
                          ON programa.cod_setorial = programa_setorial.cod_setorial
                  INNER JOIN ppa.macro_objetivo
                          ON macro_objetivo.cod_macro = programa_setorial.cod_macro
                  INNER JOIN ppa.ppa
                          ON macro_objetivo.cod_ppa = ppa.cod_ppa

                   LEFT JOIN ppa.acao_meta_fisica_realizada AS realizada_1
                          ON realizada_1.cod_acao             = ano1.cod_acao
                         AND realizada_1.timestamp_acao_dados = ano1.timestamp_acao_dados
                         AND realizada_1.cod_recurso          = ano1.cod_recurso
                         AND realizada_1.exercicio_recurso    = ano1.exercicio_recurso
                         AND realizada_1.ano                  = ano1.ano
                   LEFT JOIN ppa.acao_meta_fisica_realizada AS realizada_2
                          ON realizada_2.cod_acao             = ano2.cod_acao
                         AND realizada_2.timestamp_acao_dados = ano2.timestamp_acao_dados
                         AND realizada_2.cod_recurso          = ano2.cod_recurso
                         AND realizada_2.exercicio_recurso    = ano2.exercicio_recurso
                         AND realizada_2.ano                  = ano2.ano
                   LEFT JOIN ppa.acao_meta_fisica_realizada AS realizada_3
                          ON realizada_3.cod_acao             = ano3.cod_acao
                         AND realizada_3.timestamp_acao_dados = ano3.timestamp_acao_dados
                         AND realizada_3.cod_recurso          = ano3.cod_recurso
                         AND realizada_3.exercicio_recurso    = ano3.exercicio_recurso
                         AND realizada_3.ano                  = ano3.ano
                   LEFT JOIN ppa.acao_meta_fisica_realizada AS realizada_4
                          ON realizada_4.cod_acao             = ano4.cod_acao
                         AND realizada_4.timestamp_acao_dados = ano4.timestamp_acao_dados
                         AND realizada_4.cod_recurso          = ano4.cod_recurso
                         AND realizada_4.exercicio_recurso    = ano4.exercicio_recurso
                         AND realizada_4.ano                  = ano4.ano

                   WHERE ano1.ano = :ano
                     AND :exercicio::INTEGER BETWEEN ppa.ano_inicio::INTEGER AND ppa.ano_final::INTEGER
                     AND ano1.timestamp_acao_dados = ( SELECT MAX(timestamp_acao_dados)
                                                         FROM ppa.acao_recurso AS AR
                                                        WHERE AR.cod_acao = ano1.cod_acao
                                                          AND AR.cod_recurso = ano1.cod_recurso
                                                          AND AR.exercicio_recurso = ano1.exercicio_recurso
                                                          AND AR.ano = ano1.ano )
                  GROUP BY ano1.cod_acao
                        , acao.num_acao
                        , ano1.timestamp_acao_dados
                        , recurso.masc_recurso
                        , ano1.cod_recurso
                        , recurso.nom_recurso
                        , ano1.exercicio_recurso
                        , ano2.exercicio_recurso
                        , ano3.exercicio_recurso
                        , ano4.exercicio_recurso
                        , ano1.valor
                        , ano2.valor
                        , ano3.valor
                        , ano4.valor
                        , qtd_ano1.quantidade
                        , qtd_ano2.quantidade
                        , qtd_ano3.quantidade
                        , qtd_ano4.quantidade
                        , ppa.cod_ppa
                        , realizada_1.valor
                        , realizada_2.valor
                        , realizada_3.valor
                        , realizada_4.valor
                        , realizada_1.justificativa
                        , realizada_2.justificativa
                        , realizada_3.justificativa
                        , realizada_4.justificativa
                  ORDER BY acao.num_acao, ano1.cod_recurso"
            )
        );
        $query->bindValue('ano', (string) $ano);
        $query->bindValue('exercicio', $exercicio);
        $query->execute();
        return $query->fetchAll();
    }
}
