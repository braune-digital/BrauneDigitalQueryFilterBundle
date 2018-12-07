<?php

namespace BrauneDigital\QueryFilterBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;

class EntityToEqualTransformer extends ChoiceToEqualTransformer
{
    public function reverseTransform($choice)
    {
        if (is_array($choice) || $choice instanceof ArrayCollection) {
            $choices = array();
            foreach($choice as $item) {
                $choices[] = $item->getId();
            }
            $choice = $choices;
        } else if (is_object($choice)) {
            $choice = $choice->getId();
        }
        return parent::reverseTransform($choice);
    }
}