<?php

namespace BrauneDigital\QueryFilterBundle\Form\DataTransformer;

class TextToEqualTransformer extends BaseTransformer
{
    public function transform($filter)
    {
        //TODO: we could check for property mismatches
        if (!empty($filter)  && is_array($filter) && isset($filter['value'])) {
            return $filter['value'];
        } else {
            return "";
        }
    }

    public function reverseTransform($text)
    {
        if($text && isset($this->options['property'])) {
            return array(
                'property' => $this->options['property'],
                'filter' => 'equal',
                'value' => $text
            );
        } else {
            return array();
        }
    }
}