<?php
 
namespace Urbem\CoreBundle\Entity\Almoxarifado;

/**
 * ControleEstoque
 */
class ControleEstoque
{
    /**
     * PK
     * @var integer
     */
    private $codItem;

    /**
     * @var integer
     */
    private $estoqueMinimo = 0;

    /**
     * @var integer
     */
    private $estoqueMaximo = 0;

    /**
     * @var integer
     */
    private $pontoPedido = 0;

    /**
     * OneToOne (owning side)
     * @var \Urbem\CoreBundle\Entity\Almoxarifado\CatalogoItem
     */
    private $fkAlmoxarifadoCatalogoItem;


    /**
     * Set codItem
     *
     * @param integer $codItem
     * @return ControleEstoque
     */
    public function setCodItem($codItem)
    {
        $this->codItem = $codItem;
        return $this;
    }

    /**
     * Get codItem
     *
     * @return integer
     */
    public function getCodItem()
    {
        return $this->codItem;
    }

    /**
     * Set estoqueMinimo
     *
     * @param integer $estoqueMinimo
     * @return ControleEstoque
     */
    public function setEstoqueMinimo($estoqueMinimo)
    {
        $this->estoqueMinimo = $estoqueMinimo;
        return $this;
    }

    /**
     * Get estoqueMinimo
     *
     * @return integer
     */
    public function getEstoqueMinimo()
    {
        return $this->estoqueMinimo;
    }

    /**
     * Set estoqueMaximo
     *
     * @param integer $estoqueMaximo
     * @return ControleEstoque
     */
    public function setEstoqueMaximo($estoqueMaximo)
    {
        $this->estoqueMaximo = $estoqueMaximo;
        return $this;
    }

    /**
     * Get estoqueMaximo
     *
     * @return integer
     */
    public function getEstoqueMaximo()
    {
        return $this->estoqueMaximo;
    }

    /**
     * Set pontoPedido
     *
     * @param integer $pontoPedido
     * @return ControleEstoque
     */
    public function setPontoPedido($pontoPedido)
    {
        $this->pontoPedido = $pontoPedido;
        return $this;
    }

    /**
     * Get pontoPedido
     *
     * @return integer
     */
    public function getPontoPedido()
    {
        return $this->pontoPedido;
    }

    /**
     * OneToOne (owning side)
     * Set AlmoxarifadoCatalogoItem
     *
     * @param \Urbem\CoreBundle\Entity\Almoxarifado\CatalogoItem $fkAlmoxarifadoCatalogoItem
     * @return ControleEstoque
     */
    public function setFkAlmoxarifadoCatalogoItem(\Urbem\CoreBundle\Entity\Almoxarifado\CatalogoItem $fkAlmoxarifadoCatalogoItem)
    {
        $this->codItem = $fkAlmoxarifadoCatalogoItem->getCodItem();
        $this->fkAlmoxarifadoCatalogoItem = $fkAlmoxarifadoCatalogoItem;
        return $this;
    }

    /**
     * OneToOne (owning side)
     * Get fkAlmoxarifadoCatalogoItem
     *
     * @return \Urbem\CoreBundle\Entity\Almoxarifado\CatalogoItem
     */
    public function getFkAlmoxarifadoCatalogoItem()
    {
        return $this->fkAlmoxarifadoCatalogoItem;
    }
}
