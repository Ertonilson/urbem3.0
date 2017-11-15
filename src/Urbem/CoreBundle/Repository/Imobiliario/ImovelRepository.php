<?php

namespace Urbem\CoreBundle\Repository\Imobiliario;

use Doctrine\ORM;

/**
 * Class ImovelRepository
 * @package Urbem\CoreBundle\Repository\Imobiliario
 */
class ImovelRepository extends ORM\EntityRepository
{
    /**
     * @param $params
     * @return array
     */
    public function findImoveis($params)
    {
        $sql = "
            SELECT
                I.INSCRICAO_MUNICIPAL,
                IP.NUMCGM
            FROM IMOBILIARIO.IMOVEL AS I LEFT JOIN (
                SELECT BAL.*
                FROM imobiliario.baixa_imovel AS BAL,
                (
                    SELECT
                        MAX( timestamp ) AS timestamp,
                        inscricao_municipal
                    FROM
                        imobiliario.baixa_imovel
                    GROUP BY
                        inscricao_municipal
                ) AS BT
                WHERE
                    BAL.inscricao_municipal = BT.inscricao_municipal
                    AND BAL.timestamp = BT.timestamp
            ) bi ON I.inscricao_municipal = bi.inscricao_municipal
            LEFT JOIN  imobiliario.proprietario AS IP ON IP.inscricao_municipal = I.inscricao_municipal
            WHERE ( ( bi.dt_inicio IS NULL ) OR ( bi.dt_inicio IS NOT NULL AND bi.dt_termino IS NOT NULL )
                AND bi.inscricao_municipal = I.inscricao_municipal
            )";

        if (is_array($params) and count($params) > 0) {
            $sql .= " AND IP.numcgm = :numcgm";
        }

        $query = $this->_em->getConnection()->prepare($sql);

        if (is_array($params) and count($params) > 0) {
            $query->bindValue('numcgm', $params['numcgm'], \PDO::PARAM_INT);
        }

        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $params
     * @param bool $calculo
     * @return array
     */
    public function consultar($params, $calculo = false)
    {
        $param1 = $param2 = $param3 = $param4 = '';
        if ((integer) $params['inscricaoMunicipal']) {
            $param1 .= sprintf(" AND I.inscricao_municipal = %d ", $params['inscricaoMunicipal']);
        }

        if (isset($params['valor']) && ($params['valor'])) {
            $param1 .= sprintf(" AND ltrim(LL.valor,''0'') = ''%s'' ", $params['valor']);
        }

        if (isset($params['codigoComposto']) && ($params['codigoComposto'])) {
            $param1 .= sprintf(" AND LOC.codigo_composto like ''%s%%'' ", $params['codigoComposto']);
        }

        if (isset($params['numcgm']) && ((integer) $params['numcgm'])) {
            $param1 .= sprintf(" AND C.numcgm = %d ", $params['numcgm']);
        }

        if (isset($params['numero']) && ($params['numero'])) {
            $param1 .= sprintf(" AND ltrim ( I.numero, ''0'' ) = ''%s'' ", $params['numero']);
        }

        if (isset($params['complemento']) && ($params['complemento'])) {
            $param1 .= sprintf(" AND UPPER( I.complemento ) like UPPER( ''%s%%'' )", $params['complemento']);
        }

        if (isset($params['codLogradouro']) && ((integer) $params['codLogradouro'])) {
            $param2 .= sprintf(" AND LO.cod_logradouro = %d ", $params['codLogradouro']);
        }

        if (isset($params['codBairro']) && ((integer) $params['codBairro'])) {
            $param2 .= sprintf(" AND B.cod_bairro = %d ", $params['codBairro']);
        }

        if (isset($params['codCondominio']) && ((integer) $params['codCondominio'])) {
            $param2 .= sprintf(" AND ICO.cod_condominio = %d", $params['codCondominio']);
        }

        if ($calculo) {
            $param3 = ' GROUP BY cod_lote ';
            $param4 = ' GROUP BY cod_construcao, cod_tipo ';
        }

        $sql = "
            SELECT *
            FROM imobiliario.fn_rl_cadastro_imobiliario(
                '". $param1 ."',
                '". $param2 ."',
                'TRUE',
                ' GROUP BY inscricao_municipal ',
                '". $param3 ."',
                '". $param4 ."'
            ) as retorno(
                inscricao_municipal  integer,
                proprietario_cota    text,
                cod_lote             integer,
                dt_cadastro          date,
                tipo_lote            text,
                valor_lote           varchar,
                endereco             varchar,
                cep                  varchar,
                cod_localizacao      integer,
                localizacao          text,
                cod_condominio       integer,
                creci                varchar,
                nom_bairro           varchar,
                logradouro           text,
                situacao             text
            )
         ";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $inscricaoMunicipal
     * @return mixed
     */
    public function consultaAreaImovel($inscricaoMunicipal)
    {
        $sql = 'SELECT fn_calcula_area_imovel as area_imovel FROM imobiliario.fn_calcula_area_imovel(:inscricaoMunicipal)';
        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue('inscricaoMunicipal', $inscricaoMunicipal, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn();
    }

    /**
     * @param $inscricaoMunicipal
     * @return mixed
     */
    public function consultaAreaImovelLote($inscricaoMunicipal)
    {
        $sql = 'SELECT fn_calcula_area_imovel_lote as area_imovel_lote FROM imobiliario.fn_calcula_area_imovel_lote(:inscricaoMunicipal)';
        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue('inscricaoMunicipal', $inscricaoMunicipal, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn();
    }

    /**
     * @param $inscricaoMunicipal
     * @return mixed
     */
    public function consultaFracaoIdeal($inscricaoMunicipal)
    {
        $sql = 'SELECT calculafracaoideal as area_imovel_lote FROM public.calculafracaoideal(:inscricaoMunicipal)';
        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue('inscricaoMunicipal', $inscricaoMunicipal, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn();
    }

    public function getInscricaoImobiliariabyCodAndLoteAndLocalizacao($params)
    {
        $sql = "SELECT
	            imovel.inscricao_municipal,
	            localizacao.codigo_composto AS valor_composto,
	            lote_localizacao.valor,
	            sw_tipo_logradouro.nom_tipo||' '||sw_nome_logradouro.nom_logradouro as logradouro,
	            imovel.numero,
	            imovel.complemento
	        FROM
	            imobiliario.imovel
	        LEFT JOIN
	            (
	                SELECT
	                    tmp.*
	                FROM
	                    imobiliario.baixa_imovel AS tmp
	                INNER JOIN
	                    (
	                        SELECT
	                            max(timestamp) AS timestamp,
	                            inscricao_municipal
	                        FROM
	                            imobiliario.baixa_imovel
	                        GROUP BY
	                            inscricao_municipal
	                    )AS tmp2
	                ON
	                    tmp2.inscricao_municipal = tmp.inscricao_municipal
	                    AND tmp2.timestamp = tmp.timestamp
	            )AS baixa_imovel
	        ON
	            baixa_imovel.inscricao_municipal = imovel.inscricao_municipal
	        INNER JOIN
	            imobiliario.imovel_confrontacao
	        ON
	            imovel_confrontacao.inscricao_municipal = imovel.inscricao_municipal
	        INNER JOIN
	            imobiliario.confrontacao_trecho
	        ON
	            confrontacao_trecho.cod_confrontacao = imovel_confrontacao.cod_confrontacao
	            AND confrontacao_trecho.cod_lote = imovel_confrontacao.cod_lote
	            AND confrontacao_trecho.principal = true
	        INNER JOIN
	            sw_nome_logradouro
	        ON
	            sw_nome_logradouro.cod_logradouro = confrontacao_trecho.cod_logradouro
	        INNER JOIN
	            sw_tipo_logradouro
	        ON
	            sw_tipo_logradouro.cod_tipo = sw_nome_logradouro.cod_tipo

	        INNER JOIN
	            (
	                SELECT
	                    tmp.*
	                FROM
	                    imobiliario.imovel_lote AS tmp
	                INNER JOIN
	                    (
	                        SELECT
	                            inscricao_municipal,
	                            max(timestamp) AS timestamp
	                        FROM
	                            imobiliario.imovel_lote
	                        GROUP BY
	                            inscricao_municipal
	                    )AS tmp2
	                ON
	                    tmp2.inscricao_municipal = tmp.inscricao_municipal
	                    AND tmp2.timestamp = tmp.timestamp
	            )AS imovel_lote
	        ON
	            imovel_lote.inscricao_municipal = imovel.inscricao_municipal
	        INNER JOIN
	            imobiliario.lote_localizacao
	        ON
	            lote_localizacao.cod_lote = imovel_lote.cod_lote
	        INNER JOIN
	            imobiliario.localizacao
	        ON
	            localizacao.cod_localizacao = lote_localizacao.cod_localizacao
	        LEFT JOIN
	            (
	                SELECT
	                    tmp.*
	                FROM
	                    imobiliario.baixa_localizacao AS tmp
	                INNER JOIN
	                    (
	                        SELECT
	                            max(timestamp) AS timestamp,
	                            cod_localizacao
	                        FROM
	                            imobiliario.baixa_localizacao
	                        GROUP BY
	                            cod_localizacao
	                    )AS tmp2
	                ON
	                    tmp2.cod_localizacao = tmp.cod_localizacao
	                    AND tmp2.timestamp = tmp.timestamp
	            )AS baixa_localizacao
	        ON
	            baixa_localizacao.cod_localizacao = localizacao.cod_localizacao
	        WHERE
	            (baixa_imovel.dt_inicio IS NULL OR (baixa_imovel.dt_inicio IS NOT NULL AND baixa_imovel.dt_termino IS NOT NULL ))
	            AND (baixa_localizacao.dt_inicio IS NULL OR (baixa_localizacao.dt_inicio IS NOT NULL AND baixa_localizacao.dt_termino IS NOT NULL ))";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();
    }

    /**
     * @param $inscricaoMunicipal
     * @param $numcgm
     * @return mixed
     */
    public function getConsultaFinanceiraImovel($numcgm, $inscricaoMunicipal, $exercicio)
    {
        $sql = 'SELECT ii.inscricao_municipal
                , arrecadacao.fn_consulta_endereco_imovel(ii.inscricao_municipal) as dados
                , cgm.numcgm
                , cgm.nom_cgm
                , ip.cota
             from imobiliario.imovel ii
        inner join (    select inscricao_municipal
                            , max(timestamp)
                         from arrecadacao.imovel_v_venal
                     group by inscricao_municipal) aiv
               on aiv.inscricao_municipal = ii.inscricao_municipal
        INNER JOIN ( select inscricao_municipal
                     , max(numcgm)  as numcgm
                     , max(cota) as cota
                from imobiliario.proprietario
            group by inscricao_municipal
                 ) ip on  ip.inscricao_municipal = ii.inscricao_municipal
        INNER JOIN sw_cgm cgm ON cgm.numcgm = ip.numcgm
        WHERE
         %s
        order by cota desc';

        $where[] = '1=1';

        if ($inscricaoMunicipal) {
            $where[] = 'ii.inscricao_municipal =:inscricaoMunicipal';
        }

        if ($numcgm) {
            $where[] = 'cgm.numcgm = :numcgm';
        }

        $query = $this->_em->getConnection()->prepare(sprintf($sql, implode(' AND ', $where)));

        if ($inscricaoMunicipal) {
            $query->bindValue(':inscricaoMunicipal', $inscricaoMunicipal, \PDO::PARAM_INT);
        }

        if ($numcgm) {
            $query->bindValue(':numcgm', $numcgm, \PDO::PARAM_INT);
        }

        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $inscricaoMunicipal
     * @return mixed
     */
    public function getSituacaoImovel($inscricaoMunicipal)
    {
        $sql = 'SELECT
         imobiliario.fn_busca_situacao_imovel(:inscricaoMunicipal,\'' . date('Y-m-d') . '\') as valor';

        $query = $this->_em->getConnection()->prepare($sql);

        $query->bindValue(':inscricaoMunicipal', $inscricaoMunicipal, \PDO::PARAM_INT);

        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $inscricaoMunicipal
     * @return mixed
     */
    public function getListaValorVenal($inscricaoMunicipal)
    {
        $sql = 'SELECT
         IV.INSCRICAO_MUNICIPAL,
         to_char(IV.TIMESTAMP,\'dd/mm/YYYY hh:mm\') as data,
         case when IV.VENAL_TOTAL_INFORMADO IS NOT NULL then
             IV.VENAL_TERRITORIAL_INFORMADO
         else
             IV.VENAL_TERRITORIAL_CALCULADO
         end AS venal_territorial,
         case when IV.VENAL_TOTAL_INFORMADO IS NOT NULL then
             IV.VENAL_PREDIAL_INFORMADO
         else
             IV.VENAL_PREDIAL_CALCULADO
         end AS venal_predial,
         case when IV.VENAL_TOTAL_INFORMADO IS NOT NULL then
             IV.VENAL_TOTAL_INFORMADO
         else
             IV.VENAL_TOTAL_CALCULADO
         end AS venal_total,
         case
             when IV.VENAL_TOTAL_INFORMADO IS NOT NULL then
                 \'Informado\'::varchar
             else
                 \'Calculado\'::varchar
         end as tipo,
         IV.EXERCICIO
        FROM
         arrecadacao.imovel_v_venal AS IV
        WHERE inscricao_municipal = :inscricaoMunicipal order by IV.TIMESTAMP desc';

        $query = $this->_em->getConnection()->prepare($sql);

        $query->bindValue(':inscricaoMunicipal', $inscricaoMunicipal, \PDO::PARAM_INT);

        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

     /**
     * @param $inscricaoMunicipal
     * @return mixed
     */
    public function getListaLancamentos($inscricaoMunicipal)
    {
        $sql = 'SELECT
         lancamentos.*
         FROM
        (
             select DISTINCT grupo.cod_grupo,
             al.cod_lancamento,
             grupo.cod_modulo,
             case
                  when grupo.cod_modulo is not  null then grupo.descricao ||\'/\'||grupo.ano_exercicio
                  else mc.descricao_credito
             end as origem,
             (
              case
                  when ic.cod_calculo  is not null then ic.inscricao_municipal
                  when cec.cod_calculo is not null then cec.inscricao_economica
               end
             ) as inscricao,
             ( arrecadacao.buscaCgmLancamento (alc.cod_lancamento)||\' - \'||
               arrecadacao.buscaContribuinteLancamento(alc.cod_lancamento)
            )::varchar as proprietarios,
             case
             when ic.cod_calculo  is not null then
                 arrecadacao.fn_consulta_endereco_imovel(ic.inscricao_municipal)
             when cec.cod_calculo is not null then
                 arrecadacao.fn_consulta_endereco_empresa(cec.inscricao_economica)
             else \'Nao Encontrado\'
             end as dados_complementares,
             arrecadacao.fn_ultimo_venal_por_im_lanc(ic.inscricao_municipal, alc.cod_lancamento) as venal,
             coalesce(al.total_parcelas,0)::int as num_parcelas,
             coalesce(arrecadacao.fn_num_unicas(alc.cod_lancamento),0)::int as num_unicas,
              arrecadacao.buscaValorLancadoLancamento ( al.cod_lancamento, ac.exercicio)::numeric(14,2) as valor_lancamento,
              arrecadacao.buscaValorCalculadoLancamento ( al.cod_lancamento, ac.exercicio)::numeric(14,2) as valor_calculado,
             to_char(ac.timestamp, \'dd/mm/yyyy HH12:MI\' ) AS timestamp_calculo,
             case  when ac.calculado = true then \'Calculado\'::text
             else \'Manual\'::text
             end as tipo_calculo
         FROM
             arrecadacao.calculo_cgm cgm
             INNER JOIN arrecadacao.calculo ac ON cgm.cod_calculo = ac.cod_calculo
             INNER JOIN arrecadacao.lancamento_calculo as alc ON ac.cod_calculo = alc.cod_calculo
             INNER JOIN arrecadacao.lancamento as al ON al.cod_lancamento = alc.cod_lancamento
             LEFT JOIN   (    SELECT gc.cod_grupo, gc.descricao, gc.ano_exercicio, cgc.cod_calculo, m.cod_modulo
                                   FROM arrecadacao.calculo_grupo_credito cgc
                                   INNER JOIN arrecadacao.grupo_credito gc ON gc.cod_grupo     = cgc.cod_grupo
                                                                                                           AND gc.ano_exercicio = cgc.ano_exercicio
                                   INNER JOIN administracao.modulo m       ON m.cod_modulo     = gc.cod_modulo
                               ) as grupo ON grupo.cod_calculo = ac.cod_calculo AND grupo.ano_exercicio = ac.exercicio
            LEFT JOIN arrecadacao.imovel_calculo ic                             ON ic.cod_calculo     = ac.cod_calculo
            LEFT JOIN arrecadacao.cadastro_economico_calculo cec  ON cec.cod_calculo  = ac.cod_calculo
            INNER JOIN monetario.credito mc ON mc.cod_credito = ac.cod_credito
                             AND mc.cod_especie = ac.cod_especie AND mc.cod_genero = ac.cod_genero
                             AND mc.cod_natureza = ac.cod_natureza

             WHERE
        ic.inscricao_municipal = :inscricaoMunicipal
        ORDER BY al.cod_lancamento DESC
         ) as lancamentos
         ';

        $query = $this->_em->getConnection()->prepare($sql);

        $query->bindValue(':inscricaoMunicipal', $inscricaoMunicipal, \PDO::PARAM_INT);

        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $inscricaoMunicipal
     * @return mixed
     */
    public function getListaCalculosNaoLancados($inscricaoMunicipal)
    {
        $sql = 'SELECT to_char(ac.timestamp,\'dd/mm/YYYY HH24:MI\')  as data
                , aic.inscricao_municipal
                , ac.cod_calculo
                , ac.valor as vlr
                , ac.exercicio
                , (mc.cod_credito||\'.\'||
                 mc.cod_especie ||\'.\'||
                 mc.cod_genero  ||\'.\'||
                 mc.cod_natureza||\' \'||
                 mc.descricao_credito) as credito
             from arrecadacao.imovel_calculo aic
       inner join arrecadacao.calculo ac
               on aic.cod_calculo = ac.cod_calculo
       inner join monetario.credito mc
               on mc.cod_credito = ac.cod_credito
              and mc.cod_especie = ac.cod_especie
              and mc.cod_genero  = ac.cod_genero
              and mc.cod_natureza= ac.cod_natureza
        left join arrecadacao.lancamento_calculo alc
               on alc.cod_calculo = ac.cod_calculo
            where alc.cod_calculo is null
         and aic.inscricao_municipal = :inscricaoMunicipal
         order by ac.timestamp desc
        ';

        $query = $this->_em->getConnection()->prepare($sql);

        $query->bindValue(':inscricaoMunicipal', $inscricaoMunicipal, \PDO::PARAM_INT);

        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $filtroLote
     * @param $filtroImovel
     * @param $distinct
     * @param $orderBy
     * @return mixed
     */
    public function getAlteracaoCadastralList($filtroLote, $filtroImovel, $distinct, $orderBy = '')
    {
        $sql = '
            SELECT *
            FROM imobiliario.fn_rl_alteracao_cadastral( \'%s\'
                                        ,\'%s\'
                                        ,\'%s\'
            ) as retorno( inscricao_municipal  integer
                         ,proprietario_cota    text
                         ,cod_lote             integer
                         ,tipo_lote            text
                         ,numero               varchar
                         ,complemento          varchar
                         ,cod_localizacao      integer
                         ,localizacao          text
                         ,cod_condominio       integer
                         ,creci                varchar
                         ,nom_bairro           varchar
                         ,logradouro           text
                         ,situacao             text
            )
             ORDER BY  %s
        ';

        $filtro1 = '';
        if ($filtroLote) {
            $filtro1 = ' AND ' . implode(' AND ', $filtroLote);
        }

        $filtro2 = '';
        if ($filtroImovel) {
            $filtro2 = ' AND ' . implode(' AND ', $filtroImovel);
        }

        $filtro3 = '';

        switch ($orderBy) {
            case 'inscricao':
                $orderBy = ' inscricao_municipal, localizacao';
                break;
            case 'localizacao':
                $orderBy = ' localizacao, inscricao_municipal';
                break;
            case 'lote':
                $orderBy = ' cod_lote, localizacao';
                break;
            case 'logradouro':
                $orderBy = ' nom_logradouro, inscricao_municipal';
                break;
            case 'bairro':
                $orderBy = ' nom_bairro, inscricao_municipal';
                break;
            default:
                $orderBy = ' inscricao_municipal, localizacao';
        }

        $sql = sprintf($sql, $filtro1, $filtro2, $filtro3, $orderBy);

        $query = $this->_em->getConnection()->prepare($sql);

        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * @param array $filtro
     * @param string $situacao
     * @param string $orderBy
     * @param boolean $distinct
     * @return array
     */
    public function getCadastroList(Array $filtro, $situacao = null, $orderBy = '', $distinct = true)
    {
        $sql = "
            SELECT * FROM 
	           imobiliario.fn_rl_cadastro_imobiliario( 
	               :lote,
                   :imovel,
                   :distinct,
                   :atribImovel,
                   :atribLote,
                   :atribEdf
	           ) as retorno(
                   inscricao_municipal  integer,
                   proprietario_cota    text,
                   cod_lote             integer,
                   dt_cadastro          date,
                   tipo_lote            text,
                   valor_lote           varchar,
                   endereco             varchar,
                   cep                  varchar,
                   cod_localizacao      integer,
                   localizacao          text,
                   cod_condominio       integer,
                   creci                varchar,
                   nom_bairro           varchar,
                   logradouro           text,
                   situacao             text
	           )
        ";
        
        $hasSituacao = $situacao != "todos" && !is_null($situacao);
        
        if ($hasSituacao) {
            $sql .= " WHERE situacao = :situacao";
        }
        
        switch ($orderBy) {
            case 'inscricao':
                $orderBy = ' inscricao_municipal, localizacao';
                break;
            case 'localizacao':
                $orderBy = ' localizacao, inscricao_municipal';
                break;
            case 'lote':
                $orderBy = ' cod_lote, localizacao';
                break;
            case 'logradouro':
                $orderBy = ' nom_logradouro, inscricao_municipal';
                break;
            case 'bairro':
                $orderBy = ' nom_bairro, inscricao_municipal';
                break;
            default:
                $orderBy = ' inscricao_municipal, localizacao';
        }
        
        $lote = $imovel = $atribImovel = $atribLote = $atribEdf = '';
        
        if (isset($filtro['lote']) && count($filtro['lote'])) {
            $lote = ' AND ' . implode(' AND ', $filtro['lote']);
        }
        if (isset($filtro['imovel']) && count($filtro['imovel'])) {
            $imovel = ' AND ' . implode(' AND ', $filtro['imovel']);
        }
        if (isset($filtro['atribImovel']) && count($filtro['atribImovel'])) {
            $atribImovel = ' AND ' . implode(' AND ', $filtro['atribImovel']);
        }
        if (isset($filtro['atribEdf']) && count($filtro['atribEdf'])) {
            $atribEdf = ' AND ' . implode(' AND ', $filtro['atribEdf']);
        }
        
        $sql .= sprintf(" ORDER BY %s", $orderBy);
        
        $query = $this->_em->getConnection()->prepare($sql);
        
        $query->bindParam(":lote", $lote);
        $query->bindParam(":imovel", $imovel);
        $query->bindParam(":distinct", $distinct);
        $query->bindParam(":atribImovel", $atribImovel);
        $query->bindParam(":atribLote", $atribLote);
        $query->bindParam(":atribEdf", $atribEdf);
        
        if ($hasSituacao) {
            $query->bindParam(":situacao", $situacao);
        }

        $query->execute();
        
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}
