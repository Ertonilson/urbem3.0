<?php
 
namespace Urbem\CoreBundle\Entity\Stn;

/**
 * ReceitaCorrenteLiquida
 */
class ReceitaCorrenteLiquida
{
    /**
     * PK
     * @var integer
     */
    private $mes;

    /**
     * PK
     * @var string
     */
    private $ano;

    /**
     * PK
     * @var string
     */
    private $exercicio;

    /**
     * PK
     * @var integer
     */
    private $codEntidade;

    /**
     * @var integer
     */
    private $valor;

    /**
     * @var integer
     */
    private $valorReceitaTributaria;

    /**
     * @var integer
     */
    private $valorReceitaContribuicoes;

    /**
     * @var integer
     */
    private $valorReceitaPatrimonial;

    /**
     * @var integer
     */
    private $valorReceitaAgropecuaria;

    /**
     * @var integer
     */
    private $valorReceitaIndustrial;

    /**
     * @var integer
     */
    private $valorReceitaServicos;

    /**
     * @var integer
     */
    private $valorTransferenciasCorrentes;

    /**
     * @var integer
     */
    private $valorOutrasReceitas;

    /**
     * @var integer
     */
    private $valorContribPlanoSss;

    /**
     * @var integer
     */
    private $valorCompensacaoFinanceira;

    /**
     * @var integer
     */
    private $valorDeducaoFundeb;

    /**
     * ManyToOne
     * @var \Urbem\CoreBundle\Entity\Orcamento\Entidade
     */
    private $fkOrcamentoEntidade;


    /**
     * Set mes
     *
     * @param integer $mes
     * @return ReceitaCorrenteLiquida
     */
    public function setMes($mes)
    {
        $this->mes = $mes;
        return $this;
    }

    /**
     * Get mes
     *
     * @return integer
     */
    public function getMes()
    {
        return $this->mes;
    }

    /**
     * Set ano
     *
     * @param string $ano
     * @return ReceitaCorrenteLiquida
     */
    public function setAno($ano)
    {
        $this->ano = $ano;
        return $this;
    }

    /**
     * Get ano
     *
     * @return string
     */
    public function getAno()
    {
        return $this->ano;
    }

    /**
     * Set exercicio
     *
     * @param string $exercicio
     * @return ReceitaCorrenteLiquida
     */
    public function setExercicio($exercicio)
    {
        $this->exercicio = $exercicio;
        return $this;
    }

    /**
     * Get exercicio
     *
     * @return string
     */
    public function getExercicio()
    {
        return $this->exercicio;
    }

    /**
     * Set codEntidade
     *
     * @param integer $codEntidade
     * @return ReceitaCorrenteLiquida
     */
    public function setCodEntidade($codEntidade)
    {
        $this->codEntidade = $codEntidade;
        return $this;
    }

    /**
     * Get codEntidade
     *
     * @return integer
     */
    public function getCodEntidade()
    {
        return $this->codEntidade;
    }

    /**
     * Set valor
     *
     * @param integer $valor
     * @return ReceitaCorrenteLiquida
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * Get valor
     *
     * @return integer
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set valorReceitaTributaria
     *
     * @param integer $valorReceitaTributaria
     * @return ReceitaCorrenteLiquida
     */
    public function setValorReceitaTributaria($valorReceitaTributaria = null)
    {
        $this->valorReceitaTributaria = $valorReceitaTributaria;
        return $this;
    }

    /**
     * Get valorReceitaTributaria
     *
     * @return integer
     */
    public function getValorReceitaTributaria()
    {
        return $this->valorReceitaTributaria;
    }

    /**
     * Set valorReceitaContribuicoes
     *
     * @param integer $valorReceitaContribuicoes
     * @return ReceitaCorrenteLiquida
     */
    public function setValorReceitaContribuicoes($valorReceitaContribuicoes = null)
    {
        $this->valorReceitaContribuicoes = $valorReceitaContribuicoes;
        return $this;
    }

    /**
     * Get valorReceitaContribuicoes
     *
     * @return integer
     */
    public function getValorReceitaContribuicoes()
    {
        return $this->valorReceitaContribuicoes;
    }

    /**
     * Set valorReceitaPatrimonial
     *
     * @param integer $valorReceitaPatrimonial
     * @return ReceitaCorrenteLiquida
     */
    public function setValorReceitaPatrimonial($valorReceitaPatrimonial = null)
    {
        $this->valorReceitaPatrimonial = $valorReceitaPatrimonial;
        return $this;
    }

    /**
     * Get valorReceitaPatrimonial
     *
     * @return integer
     */
    public function getValorReceitaPatrimonial()
    {
        return $this->valorReceitaPatrimonial;
    }

    /**
     * Set valorReceitaAgropecuaria
     *
     * @param integer $valorReceitaAgropecuaria
     * @return ReceitaCorrenteLiquida
     */
    public function setValorReceitaAgropecuaria($valorReceitaAgropecuaria = null)
    {
        $this->valorReceitaAgropecuaria = $valorReceitaAgropecuaria;
        return $this;
    }

    /**
     * Get valorReceitaAgropecuaria
     *
     * @return integer
     */
    public function getValorReceitaAgropecuaria()
    {
        return $this->valorReceitaAgropecuaria;
    }

    /**
     * Set valorReceitaIndustrial
     *
     * @param integer $valorReceitaIndustrial
     * @return ReceitaCorrenteLiquida
     */
    public function setValorReceitaIndustrial($valorReceitaIndustrial = null)
    {
        $this->valorReceitaIndustrial = $valorReceitaIndustrial;
        return $this;
    }

    /**
     * Get valorReceitaIndustrial
     *
     * @return integer
     */
    public function getValorReceitaIndustrial()
    {
        return $this->valorReceitaIndustrial;
    }

    /**
     * Set valorReceitaServicos
     *
     * @param integer $valorReceitaServicos
     * @return ReceitaCorrenteLiquida
     */
    public function setValorReceitaServicos($valorReceitaServicos = null)
    {
        $this->valorReceitaServicos = $valorReceitaServicos;
        return $this;
    }

    /**
     * Get valorReceitaServicos
     *
     * @return integer
     */
    public function getValorReceitaServicos()
    {
        return $this->valorReceitaServicos;
    }

    /**
     * Set valorTransferenciasCorrentes
     *
     * @param integer $valorTransferenciasCorrentes
     * @return ReceitaCorrenteLiquida
     */
    public function setValorTransferenciasCorrentes($valorTransferenciasCorrentes = null)
    {
        $this->valorTransferenciasCorrentes = $valorTransferenciasCorrentes;
        return $this;
    }

    /**
     * Get valorTransferenciasCorrentes
     *
     * @return integer
     */
    public function getValorTransferenciasCorrentes()
    {
        return $this->valorTransferenciasCorrentes;
    }

    /**
     * Set valorOutrasReceitas
     *
     * @param integer $valorOutrasReceitas
     * @return ReceitaCorrenteLiquida
     */
    public function setValorOutrasReceitas($valorOutrasReceitas = null)
    {
        $this->valorOutrasReceitas = $valorOutrasReceitas;
        return $this;
    }

    /**
     * Get valorOutrasReceitas
     *
     * @return integer
     */
    public function getValorOutrasReceitas()
    {
        return $this->valorOutrasReceitas;
    }

    /**
     * Set valorContribPlanoSss
     *
     * @param integer $valorContribPlanoSss
     * @return ReceitaCorrenteLiquida
     */
    public function setValorContribPlanoSss($valorContribPlanoSss = null)
    {
        $this->valorContribPlanoSss = $valorContribPlanoSss;
        return $this;
    }

    /**
     * Get valorContribPlanoSss
     *
     * @return integer
     */
    public function getValorContribPlanoSss()
    {
        return $this->valorContribPlanoSss;
    }

    /**
     * Set valorCompensacaoFinanceira
     *
     * @param integer $valorCompensacaoFinanceira
     * @return ReceitaCorrenteLiquida
     */
    public function setValorCompensacaoFinanceira($valorCompensacaoFinanceira = null)
    {
        $this->valorCompensacaoFinanceira = $valorCompensacaoFinanceira;
        return $this;
    }

    /**
     * Get valorCompensacaoFinanceira
     *
     * @return integer
     */
    public function getValorCompensacaoFinanceira()
    {
        return $this->valorCompensacaoFinanceira;
    }

    /**
     * Set valorDeducaoFundeb
     *
     * @param integer $valorDeducaoFundeb
     * @return ReceitaCorrenteLiquida
     */
    public function setValorDeducaoFundeb($valorDeducaoFundeb = null)
    {
        $this->valorDeducaoFundeb = $valorDeducaoFundeb;
        return $this;
    }

    /**
     * Get valorDeducaoFundeb
     *
     * @return integer
     */
    public function getValorDeducaoFundeb()
    {
        return $this->valorDeducaoFundeb;
    }

    /**
     * ManyToOne (inverse side)
     * Set fkOrcamentoEntidade
     *
     * @param \Urbem\CoreBundle\Entity\Orcamento\Entidade $fkOrcamentoEntidade
     * @return ReceitaCorrenteLiquida
     */
    public function setFkOrcamentoEntidade(\Urbem\CoreBundle\Entity\Orcamento\Entidade $fkOrcamentoEntidade)
    {
        $this->exercicio = $fkOrcamentoEntidade->getExercicio();
        $this->codEntidade = $fkOrcamentoEntidade->getCodEntidade();
        $this->fkOrcamentoEntidade = $fkOrcamentoEntidade;
        
        return $this;
    }

    /**
     * ManyToOne (inverse side)
     * Get fkOrcamentoEntidade
     *
     * @return \Urbem\CoreBundle\Entity\Orcamento\Entidade
     */
    public function getFkOrcamentoEntidade()
    {
        return $this->fkOrcamentoEntidade;
    }
}
