<?php

namespace Urbem\CoreBundle\Repository\Imobiliario;

use Urbem\CoreBundle\Repository\AbstractRepository;

class ConstrucaoRepository extends AbstractRepository
{
    /**
     * @return int
     */
    public function getNextVal()
    {
        return $this->nextVal("cod_construcao");
    }

    /**
     * @param $inscricaoMunicipal
     * @return array
     */
    public function cadastroImobiliario($inscricaoMunicipal)
    {
        $sql = sprintf("
            select
                *
            from
                imobiliario.fn_rl_cadastro_imobiliario(
                    ' AND I.inscricao_municipal = %s',
                    '',
                    'TRUE',
                    '
                            GROUP BY
                                inscricao_municipal
                        ',
                    '
                            GROUP BY
                                cod_lote
                        ',
                    '
                            GROUP BY
                                cod_construcao,
                                cod_tipo
                        '
                ) as retorno(
                    inscricao_municipal integer,
                    proprietario_cota text,
                    cod_lote integer,
                    dt_cadastro date,
                    tipo_lote text,
                    valor_lote varchar,
                    endereco varchar,
                    cep varchar,
                    cod_localizacao integer,
                    localizacao text,
                    cod_condominio integer,
                    creci varchar,
                    nom_bairro varchar,
                    logradouro text,
                    situacao text
                )
            order by
                inscricao_municipal
        ", $inscricaoMunicipal);

        $query = $this->_em->getConnection()->prepare($sql);

        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $inscricaoMunicipal
     * @return array
     */
    public function areaImovel($inscricaoMunicipal)
    {
        $sql = "
            SELECT                                                                                   
                imobiliario.fn_calcula_area_imovel( inscricao_municipal ) AS area_imovel,            
                imobiliario.fn_calcula_area_imovel_lote( inscricao_municipal ) AS area_imovel_lote,  
                imobiliario.fn_calcula_area_imovel_construcao( inscricao_municipal ) AS area_total   
            FROM                                                                                     
                imobiliario.imovel                                                                   
            WHERE                                                                                    
                inscricao_municipal = :inscricaoMunicipal
        ";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue('inscricaoMunicipal', $inscricaoMunicipal, \PDO::PARAM_INT);

        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $inscricaoMunicipal
     * @return array
     */
    public function unidadeAutonoma($inscricaoMunicipal)
    {
        $sql = "
             SELECT                                                          
                 UA.*                                                        
             FROM                                                            
                 imobiliario.unidade_autonoma UA                             
             LEFT JOIN (                                                     
                SELECT                                                       
                    BAL.*                                                    
                FROM                                                         
                    imobiliario.baixa_unidade_autonoma AS BAL,               
                    (                                                        
                    SELECT                                                   
                        MAX (TIMESTAMP) AS TIMESTAMP,                        
                        inscricao_municipal,                                 
                        cod_tipo,                                            
                        cod_construcao                                       
                    FROM                                                     
                        imobiliario.baixa_unidade_autonoma                   
                    GROUP BY                                                 
                        inscricao_municipal,                                 
                        cod_tipo,                                            
                        cod_construcao                                       
                    ) AS BT                                                  
                WHERE                                                        
                    BAL.inscricao_municipal = BT.inscricao_municipal AND     
                    BAL.cod_tipo = BT.cod_tipo AND                           
                    BAL.cod_construcao = BT.cod_construcao AND               
                    BAL.timestamp = BT.timestamp                             
             ) bua                                                           
             ON                                                              
                bua.inscricao_municipal = ua.inscricao_municipal AND         
                bua.cod_tipo= ua.cod_tipo AND                                
                bua.cod_construcao = ua.cod_construcao                       
             WHERE                                                           
                ((bua.dt_inicio IS NULL) OR (bua.dt_inicio IS NOT NULL AND bua.dt_termino IS NOT NULL) AND bua.inscricao_municipal = ua.inscricao_municipal AND                        
                bua.cod_tipo= ua.cod_tipo AND                                
                bua.cod_construcao = ua.cod_construcao)                      
             AND ua.inscricao_municipal = :inscricaoMunicipal ORDER BY cod_construcao
        ";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindValue('inscricaoMunicipal', $inscricaoMunicipal, \PDO::PARAM_INT);

        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }
}
