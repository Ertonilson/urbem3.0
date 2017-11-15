<?php

namespace Urbem\CoreBundle\Repository\Arrecadacao;

use Urbem\CoreBundle\Repository\AbstractRepository;

class CalculoRepository extends AbstractRepository
{
    /**
     * @return int
     */
    public function getCodCalculo()
    {
        return $this->nextVal("cod_calculo");
    }

    /**
     * @param $inscricaoEconomica
     * @return bool|string
     */
    public function getNumCgmByInscricaoEconomica($inscricaoEconomica)
    {
        $sql = "
            SELECT DISTINCT ON (CE.inscricao_economica)
	               CE.inscricao_economica
	             , TO_CHAR(CE.dt_abertura,'dd/mm/yyyy') as dt_abertura
	             , TO_CHAR(CE.timestamp, 'dd/mm/yyyy') as dt_inclusao
	             , ACE.cod_atividade
	             , A.nom_atividade
	             , COALESCE ( A.cod_estrutural, '&nbsp;' ) as cod_estrutural
	             , CGM.nom_cgm
	             , CGMPF.cpf
	             , CGMPJ.cnpj
	             , COALESCE ( CEED.numcgm, CEEF.numcgm, CEA.numcgm ) as numcgm
	             , CASE WHEN CAST( CEEF.numcgm AS VARCHAR) IS NOT NULL THEN
	                  'F'
	               WHEN CAST( CEA.numcgm AS VARCHAR ) IS NOT NULL THEN
	                  'A'
	               WHEN CAST( CEED.numcgm AS VARCHAR) IS NOT NULL THEN
	                  'D'
	               END AS enquadramento
	             , CASE WHEN (DF.timestamp IS NOT NULL AND DI.timestamp IS NOT NULL  AND DF.timestamp >= DI.timestamp) THEN
	                  'F'
	               WHEN (DF.timestamp IS NOT NULL AND DI.timestamp IS NOT NULL  AND DF.timestamp < DI.timestamp) THEN
	                  'I'
	               WHEN (DF.timestamp IS NOT NULL) THEN
	                  'F'
	               WHEN (DI.timestamp IS NOT NULL) THEN
	                  'I'
	               END AS tipo_domicilio
	             , CEED.num_registro_junta
	             , NJ.cod_natureza
	             , NJ.nom_natureza
	             , TL.nom_tipo||' '||NL.nom_logradouro as logradouro_f
	             , TL2.nom_tipo||' '||NL2.nom_logradouro as logradouro_i
	             , I.numero AS numero_f
	             , DI.numero AS numero_i
	             , I.complemento AS complemento_f
	             , DI.complemento AS complemento_i
	             , CA.nom_categoria
	             , CASE WHEN BA.dt_inicio IS NOT NULL AND BA.dt_termino IS NULL THEN
	                   BA.timestamp
	               ELSE
	                   NULL
	               END AS dt_baixa
	             , CASE WHEN BA.dt_inicio IS NOT NULL AND BA.dt_termino IS NULL THEN
	                   BA.motivo
	               ELSE
	                   NULL
	               END AS motivo
	             , UF.nom_uf
	             , MU.nom_municipio
	             , BAI.nom_bairro
	             , BAI.cod_bairro
	             , DI.cod_logradouro
	             , DI.cep
	             , DI.caixa_postal
	             , DF.inscricao_municipal
	          FROM economico.cadastro_economico CE
	          
	     LEFT JOIN economico.atividade_cadastro_economico ACE
	            ON CE.inscricao_economica = ACE.inscricao_economica
	           AND ACE.principal = TRUE
	           
	     LEFT JOIN economico.atividade A
	            ON A.cod_atividade = ACE.cod_atividade
	            
	     LEFT JOIN economico.cadastro_economico_empresa_direito CEED
	            ON CEED.inscricao_economica = CE.inscricao_economica
	            
	     LEFT JOIN economico.cadastro_economico_empresa_fato CEEF
	            ON CEEF.inscricao_economica = CE.inscricao_economica
	            
	     LEFT JOIN economico.cadastro_economico_autonomo CEA
	            ON CEA.inscricao_economica = CE.inscricao_economica
	            
	     LEFT JOIN economico.categoria CA
	            ON CA.cod_categoria = CEED.cod_categoria
	            
	     LEFT JOIN economico.empresa_direito_natureza_juridica EDNJ
	            ON EDNJ.inscricao_economica = CEED.inscricao_economica
	            
	     LEFT JOIN economico.natureza_juridica NJ
	            ON NJ.cod_natureza = EDNJ.cod_natureza
	     
	     LEFT JOIN economico.domicilio_informado DI
	            ON DI.inscricao_economica = CE.inscricao_economica
	     
	     LEFT JOIN ( SELECT MAX(timestamp) AS timestamp                              
	                      , inscricao_economica                                     
	                   FROM economico.domicilio_fiscal                              
	               GROUP BY inscricao_economica                                      
	              ) AS DF_MAX
	           ON DF_MAX.inscricao_economica = CE.inscricao_economica          
	    
	    LEFT JOIN economico.domicilio_fiscal AS DF                             
	           ON DF.timestamp           = DF_MAX.timestamp
	          AND DF.inscricao_economica = DF_MAX.inscricao_economica
	          AND DF.inscricao_economica = CE.inscricao_economica 
	     
	     LEFT JOIN economico.sociedade S
	            ON S.inscricao_economica = CE.inscricao_economica
	     
	     LEFT JOIN imobiliario.imovel I
	            ON I.inscricao_municipal = DF.inscricao_municipal
	     
	     LEFT JOIN imobiliario.imovel_confrontacao IC
	            ON IC.inscricao_municipal = I.inscricao_municipal
	     
	     LEFT JOIN imobiliario.confrontacao_trecho CT
	            ON CT.cod_confrontacao = IC.cod_confrontacao
	           AND CT.cod_lote         = IC.cod_lote
	           AND CT.principal        = true
	     
	     LEFT JOIN sw_uf UF
	            ON UF.cod_uf = DI.cod_uf
	     
	     LEFT JOIN sw_municipio MU
	            ON MU.cod_municipio = DI.cod_municipio
	           AND MU.cod_uf        = DI.cod_uf
	     
	     LEFT JOIN sw_bairro BAI
	            ON BAI.cod_bairro    = DI.cod_bairro
	           AND BAI.cod_uf        = DI.cod_uf
	           AND BAI.cod_municipio = DI.cod_municipio
	     
	     LEFT JOIN sw_nome_logradouro NL
	            ON NL.cod_logradouro = CT.cod_logradouro
	     
	     LEFT JOIN sw_tipo_logradouro TL
	            ON TL.cod_tipo = NL.cod_tipo
	     
	     LEFT JOIN sw_nome_logradouro NL2
	            ON NL2.cod_logradouro = DI.cod_logradouro
	     
	     LEFT JOIN sw_tipo_logradouro TL2
	            ON TL2.cod_tipo = NL2.cod_tipo
	     
	     LEFT JOIN ( SELECT tmp.*
	                   FROM economico.baixa_cadastro_economico AS tmp
	             INNER JOIN ( SELECT MAX(timestamp) as timestamp
	                               , inscricao_economica
	                            FROM economico.baixa_cadastro_economico
	                        GROUP BY inscricao_economica
	                        )AS tmp2
	                     ON tmp.inscricao_economica = tmp2.inscricao_economica
	                    AND tmp.timestamp = tmp2.timestamp
	               ) AS BA
	            ON BA.inscricao_economica = CE.inscricao_economica
	             , sw_cgm AS CGM
	     
	     LEFT JOIN sw_cgm_pessoa_fisica AS CGMPF
	            ON CGMPF.numcgm = CGM.numcgm
	     
	     LEFT JOIN sw_cgm_pessoa_juridica AS CGMPJ
	            ON CGMPJ.numcgm = CGM.numcgm
	            
	         WHERE COALESCE ( CEED.numcgm, CEEF.numcgm, CEA.numcgm ) = cgm.numcgm  AND CE.inscricao_economica = :inscricaoEconomica
        ";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue('inscricaoEconomica', $inscricaoEconomica, \PDO::PARAM_INT);
        $query->execute();

        return $query->fetchColumn(9);
    }
}
