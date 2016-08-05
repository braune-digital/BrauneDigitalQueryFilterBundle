<?php

namespace BrauneDigital\QueryFilterBundle\Form\DataTransformer;

class TextToTextTransformer extends BaseTransformer
{
    public function transform($filter)
    {
        //TODO: we could check for property mismatches
        if (!empty($filter)  && is_array($filter) && isset($filter['text'])) {
            return $filter['text'];
        } else {
            return "";
        }
    }

    public function reverseTransform($text)
    {
        if($text && isset($this->options['property'])) {
            return array(
                'property' => $this->options['property'],
                'filter' => 'text',
                'text' => $text
            );
        } else {
            return array();
        }
    }
}