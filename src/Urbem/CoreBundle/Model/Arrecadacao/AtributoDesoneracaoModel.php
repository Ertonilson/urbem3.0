<?php

namespace Urbem\CoreBundle\Model\Arrecadacao;

use Doctrine\ORM;
use Urbem\CoreBundle\AbstractModel;
use Urbem\CoreBundle\Entity\Arrecadacao\AtributoDesoneracao;

/**
 * Class AtributoDesoneracaoModel
 * @package Urbem\CoreBundle\Model\Arrecadacao
 */
class AtributoDesoneracaoModel extends AbstractModel
{
    protected $entityManager = null;
    protected $repository = null;

    /**
     * AtributoDesoneracaoModel constructor.
     * @param ORM\EntityManager $entityManager
     */
    public function __construct(ORM\EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(AtributoDesoneracao::class);
    }

    /**
     * @param $params
     */
    public function saveAtributoDesoneracao($params)
    {
        $atributoDesoneracao = new AtributoDesoneracao();
        $atributoDesoneracao->setFkAdministracaoAtributoDinamico($params['atributo']);
        $atributoDesoneracao->setFkArrecadacaoDesoneracao($params['desoneracao']);

        $this->entityManager->persist($atributoDesoneracao);
        $this->entityManager->flush();
    }
}
