<?php

namespace Urbem\PrestacaoContasBundle\Form\Type;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Urbem\CoreBundle\Entity\Compras\Modalidade;

class ModalidadeType extends AbstractType
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('class', Modalidade::class);
        $resolver->setDefault('field_not_in', []);

        $resolver->setNormalizer('query_builder', function (OptionsResolver $resolver, $queryBuilder) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $resolver['em']->getRepository($resolver['class'])->createQueryBuilder('Modalidade');
            $queryBuilder->addOrderBy('Modalidade.codModalidade');
            $queryBuilder->addOrderBy('Modalidade.descricao');

            if (true === empty($resolver['field_not_in'])) {
                return $queryBuilder;
            }

            foreach ($resolver['field_not_in'] as $notIn) {
                $notInColumn = sprintf('Modalidade.%s', $notIn['column']);
                $notInValue = $notIn['value'];

                $notInExpr = $queryBuilder->expr()->notIn($notInColumn, $notInValue);

                $queryBuilder->andWhere($notInExpr);
            }

            return $queryBuilder;
        });
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
