<?php
 
namespace Urbem\CoreBundle\Entity\Almoxarifado;

/**
 * Requisicao
 */
class Requisicao
{
    /**
     * PK
     * @var string
     */
    private $exercicio;

    /**
     * PK
     * @var integer
     */
    private $codRequisicao;

    /**
     * PK
     * @var integer
     */
    private $codAlmoxarifado;

    /**
     * @var integer
     */
    private $cgmSolicitante;

    /**
     * @var integer
     */
    private $cgmRequisitante;

    /**
     * @var \DateTime
     */
    private $dtRequisicao;

    /**
     * @var string
     */
    private $observacao;

    /**
     * OneToMany
     * @var \Doctrine\Common\Collections\Collection|\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoAnulacao
     */
    private $fkAlmoxarifadoRequisicaoAnulacoes;

    /**
     * OneToMany
     * @var \Doctrine\Common\Collections\Collection|\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoHomologada
     */
    private $fkAlmoxarifadoRequisicaoHomologadas;

    /**
     * OneToMany
     * @var \Doctrine\Common\Collections\Collection|\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoItem
     */
    private $fkAlmoxarifadoRequisicaoItens;

    /**
     * ManyToOne
     * @var \Urbem\CoreBundle\Entity\Almoxarifado\Almoxarifado
     */
    private $fkAlmoxarifadoAlmoxarifado;

    /**
     * ManyToOne
     * @var \Urbem\CoreBundle\Entity\SwCgm
     */
    private $fkSwCgm;

    /**
     * ManyToOne
     * @var \Urbem\CoreBundle\Entity\Administracao\Usuario
     */
    private $fkAdministracaoUsuario;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fkAlmoxarifadoRequisicaoAnulacoes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fkAlmoxarifadoRequisicaoHomologadas = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fkAlmoxarifadoRequisicaoItens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dtRequisicao = new \DateTime;
    }

    /**
     * Set exercicio
     *
     * @param string $exercicio
     * @return Requisicao
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
     * Set codRequisicao
     *
     * @param integer $codRequisicao
     * @return Requisicao
     */
    public function setCodRequisicao($codRequisicao)
    {
        $this->codRequisicao = $codRequisicao;
        return $this;
    }

    /**
     * Get codRequisicao
     *
     * @return integer
     */
    public function getCodRequisicao()
    {
        return $this->codRequisicao;
    }

    /**
     * Set codAlmoxarifado
     *
     * @param integer $codAlmoxarifado
     * @return Requisicao
     */
    public function setCodAlmoxarifado($codAlmoxarifado)
    {
        $this->codAlmoxarifado = $codAlmoxarifado;
        return $this;
    }

    /**
     * Get codAlmoxarifado
     *
     * @return integer
     */
    public function getCodAlmoxarifado()
    {
        return $this->codAlmoxarifado;
    }

    /**
     * Set cgmSolicitante
     *
     * @param integer $cgmSolicitante
     * @return Requisicao
     */
    public function setCgmSolicitante($cgmSolicitante)
    {
        $this->cgmSolicitante = $cgmSolicitante;
        return $this;
    }

    /**
     * Get cgmSolicitante
     *
     * @return integer
     */
    public function getCgmSolicitante()
    {
        return $this->cgmSolicitante;
    }

    /**
     * Set cgmRequisitante
     *
     * @param integer $cgmRequisitante
     * @return Requisicao
     */
    public function setCgmRequisitante($cgmRequisitante)
    {
        $this->cgmRequisitante = $cgmRequisitante;
        return $this;
    }

    /**
     * Get cgmRequisitante
     *
     * @return integer
     */
    public function getCgmRequisitante()
    {
        return $this->cgmRequisitante;
    }

    /**
     * Set dtRequisicao
     *
     * @param \DateTime $dtRequisicao
     * @return Requisicao
     */
    public function setDtRequisicao(\DateTime $dtRequisicao)
    {
        $this->dtRequisicao = $dtRequisicao;
        return $this;
    }

    /**
     * Get dtRequisicao
     *
     * @return \DateTime
     */
    public function getDtRequisicao()
    {
        return $this->dtRequisicao;
    }

    /**
     * Set observacao
     *
     * @param string $observacao
     * @return Requisicao
     */
    public function setObservacao($observacao = null)
    {
        $this->observacao = $observacao;
        return $this;
    }

    /**
     * Get observacao
     *
     * @return string
     */
    public function getObservacao()
    {
        return $this->observacao;
    }

    /**
     * OneToMany (owning side)
     * Add AlmoxarifadoRequisicaoAnulacao
     *
     * @param \Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoAnulacao $fkAlmoxarifadoRequisicaoAnulacao
     * @return Requisicao
     */
    public function addFkAlmoxarifadoRequisicaoAnulacoes(\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoAnulacao $fkAlmoxarifadoRequisicaoAnulacao)
    {
        if (false === $this->fkAlmoxarifadoRequisicaoAnulacoes->contains($fkAlmoxarifadoRequisicaoAnulacao)) {
            $fkAlmoxarifadoRequisicaoAnulacao->setFkAlmoxarifadoRequisicao($this);
            $this->fkAlmoxarifadoRequisicaoAnulacoes->add($fkAlmoxarifadoRequisicaoAnulacao);
        }
        
        return $this;
    }

    /**
     * OneToMany (owning side)
     * Remove AlmoxarifadoRequisicaoAnulacao
     *
     * @param \Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoAnulacao $fkAlmoxarifadoRequisicaoAnulacao
     */
    public function removeFkAlmoxarifadoRequisicaoAnulacoes(\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoAnulacao $fkAlmoxarifadoRequisicaoAnulacao)
    {
        $this->fkAlmoxarifadoRequisicaoAnulacoes->removeElement($fkAlmoxarifadoRequisicaoAnulacao);
    }

    /**
     * OneToMany (owning side)
     * Get fkAlmoxarifadoRequisicaoAnulacoes
     *
     * @return \Doctrine\Common\Collections\Collection|\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoAnulacao
     */
    public function getFkAlmoxarifadoRequisicaoAnulacoes()
    {
        return $this->fkAlmoxarifadoRequisicaoAnulacoes;
    }

    /**
     * OneToMany (owning side)
     * Add AlmoxarifadoRequisicaoHomologada
     *
     * @param \Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoHomologada $fkAlmoxarifadoRequisicaoHomologada
     * @return Requisicao
     */
    public function addFkAlmoxarifadoRequisicaoHomologadas(\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoHomologada $fkAlmoxarifadoRequisicaoHomologada)
    {
        if (false === $this->fkAlmoxarifadoRequisicaoHomologadas->contains($fkAlmoxarifadoRequisicaoHomologada)) {
            $fkAlmoxarifadoRequisicaoHomologada->setFkAlmoxarifadoRequisicao($this);
            $this->fkAlmoxarifadoRequisicaoHomologadas->add($fkAlmoxarifadoRequisicaoHomologada);
        }
        
        return $this;
    }

    /**
     * OneToMany (owning side)
     * Remove AlmoxarifadoRequisicaoHomologada
     *
     * @param \Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoHomologada $fkAlmoxarifadoRequisicaoHomologada
     */
    public function removeFkAlmoxarifadoRequisicaoHomologadas(\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoHomologada $fkAlmoxarifadoRequisicaoHomologada)
    {
        $this->fkAlmoxarifadoRequisicaoHomologadas->removeElement($fkAlmoxarifadoRequisicaoHomologada);
    }

    /**
     * OneToMany (owning side)
     * Get fkAlmoxarifadoRequisicaoHomologadas
     *
     * @return \Doctrine\Common\Collections\Collection|\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoHomologada
     */
    public function getFkAlmoxarifadoRequisicaoHomologadas()
    {
        return $this->fkAlmoxarifadoRequisicaoHomologadas;
    }

    /**
     * OneToMany (owning side)
     * Add AlmoxarifadoRequisicaoItem
     *
     * @param \Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoItem $fkAlmoxarifadoRequisicaoItem
     * @return Requisicao
     */
    public function addFkAlmoxarifadoRequisicaoItens(\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoItem $fkAlmoxarifadoRequisicaoItem)
    {
        if (false === $this->fkAlmoxarifadoRequisicaoItens->contains($fkAlmoxarifadoRequisicaoItem)) {
            $fkAlmoxarifadoRequisicaoItem->setFkAlmoxarifadoRequisicao($this);
            $this->fkAlmoxarifadoRequisicaoItens->add($fkAlmoxarifadoRequisicaoItem);
        }
        
        return $this;
    }

    /**
     * OneToMany (owning side)
     * Remove AlmoxarifadoRequisicaoItem
     *
     * @param \Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoItem $fkAlmoxarifadoRequisicaoItem
     */
    public function removeFkAlmoxarifadoRequisicaoItens(\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoItem $fkAlmoxarifadoRequisicaoItem)
    {
        $this->fkAlmoxarifadoRequisicaoItens->removeElement($fkAlmoxarifadoRequisicaoItem);
    }

    /**
     * OneToMany (owning side)
     * Get fkAlmoxarifadoRequisicaoItens
     *
     * @return \Doctrine\Common\Collections\Collection|\Urbem\CoreBundle\Entity\Almoxarifado\RequisicaoItem
     */
    public function getFkAlmoxarifadoRequisicaoItens()
    {
        return $this->fkAlmoxarifadoRequisicaoItens;
    }

    /**
     * ManyToOne (inverse side)
     * Set fkAlmoxarifadoAlmoxarifado
     *
     * @param \Urbem\CoreBundle\Entity\Almoxarifado\Almoxarifado $fkAlmoxarifadoAlmoxarifado
     * @return Requisicao
     */
    public function setFkAlmoxarifadoAlmoxarifado(\Urbem\CoreBundle\Entity\Almoxarifado\Almoxarifado $fkAlmoxarifadoAlmoxarifado)
    {
        $this->codAlmoxarifado = $fkAlmoxarifadoAlmoxarifado->getCodAlmoxarifado();
        $this->fkAlmoxarifadoAlmoxarifado = $fkAlmoxarifadoAlmoxarifado;
        
        return $this;
    }

    /**
     * ManyToOne (inverse side)
     * Get fkAlmoxarifadoAlmoxarifado
     *
     * @return \Urbem\CoreBundle\Entity\Almoxarifado\Almoxarifado
     */
    public function getFkAlmoxarifadoAlmoxarifado()
    {
        return $this->fkAlmoxarifadoAlmoxarifado;
    }

    /**
     * ManyToOne (inverse side)
     * Set fkSwCgm
     *
     * @param \Urbem\CoreBundle\Entity\SwCgm $fkSwCgm
     * @return Requisicao
     */
    public function setFkSwCgm(\Urbem\CoreBundle\Entity\SwCgm $fkSwCgm)
    {
        $this->cgmSolicitante = $fkSwCgm->getNumcgm();
        $this->fkSwCgm = $fkSwCgm;
        
        return $this;
    }

    /**
     * ManyToOne (inverse side)
     * Get fkSwCgm
     *
     * @return \Urbem\CoreBundle\Entity\SwCgm
     */
    public function getFkSwCgm()
    {
        return $this->fkSwCgm;
    }

    /**
     * ManyToOne (inverse side)
     * Set fkAdministracaoUsuario
     *
     * @param \Urbem\CoreBundle\Entity\Administracao\Usuario $fkAdministracaoUsuario
     * @return Requisicao
     */
    public function setFkAdministracaoUsuario(\Urbem\CoreBundle\Entity\Administracao\Usuario $fkAdministracaoUsuario)
    {
        $this->cgmRequisitante = $fkAdministracaoUsuario->getNumcgm();
        $this->fkAdministracaoUsuario = $fkAdministracaoUsuario;
        
        return $this;
    }

    /**
     * ManyToOne (inverse side)
     * Get fkAdministracaoUsuario
     *
     * @return \Urbem\CoreBundle\Entity\Administracao\Usuario
     */
    public function getFkAdministracaoUsuario()
    {
        return $this->fkAdministracaoUsuario;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->observacao;
    }
}
