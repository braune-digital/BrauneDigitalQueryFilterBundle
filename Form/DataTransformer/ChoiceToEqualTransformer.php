<?php

namespace BrauneDigital\QueryFilterBundle\Form\DataTransformer;

class ChoiceToEqualTransformer extends BaseTransformer
{
    protected function allowedOptions()
    {
        return array(
            'property',
            'multiple'
        );
    }

    public function transform($filter)
    {
        //TODO: we could check for property mismatches
        if (!empty($filter)  && is_array($filter)) {
            return (isset($this->options['multiple'])&& $this->options['multiple'] )? $this->transformMultiple($filter) : $this->transformSingle($filter);
        } else {
            return null;
        }
    }

    protected function transformSingle($filter) {
        if (array_key_exists('value', $filter)) {
            return $filter['value'];
        } else {
            return null;
        }
    }

    protected function transformMultiple($filter) {
        if (array_key_exists('values', $filter)) {
            return $filter['values'];
        } else {
            return array();
        }
    }

    public function reverseTransform($choice)
    {
        if($choice && isset($this->options['property'])) {

            if (isset($this->options['multiple'])&& $this->options['multiple']) {
                return array(
                    'property' => $this->options['property'],
                    'filter' => 'equal',
                    'values' => $choice
                );
            } else {
                return array(
                    'property' => $this->options['property'],
                    'filter' => 'equal',
                    'value' => $choice
                );
            }
        } else {
            return array();
        }
    }
}