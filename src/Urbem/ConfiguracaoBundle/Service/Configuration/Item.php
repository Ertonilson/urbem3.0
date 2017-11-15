<?php

namespace Urbem\ConfiguracaoBundle\Service\Configuration;

use Doctrine\Common\Collections\ArrayCollection;

class Item extends ArrayCollection
{
    /**
     * @return string
     */
    public function getType()
    {
        return (string) $this->get('type');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (string) $this->get('name');
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [
            'mapped' => false
        ];

        foreach (['label', 'required', 'class', 'route', 'from_mapping', 'json_query_builder_fields', 'choices', 'attr', 'constraints', 'cascade_fields'] as $option) {
            if (false === $this->offsetExists($option)) {
                continue;
            }

            $options[$option] = $this->offsetGet($option);
        }
        return $options;
    }
}
