<?php

namespace BrauneDigital\QueryFilterBundle\Form\DataTransformer;

use BrauneDigital\QueryFilterBundle\Exception\InvalidOptionException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

abstract class BaseTransformer implements DataTransformerInterface
{
    protected $options;

    //we should normally only need some options here
    public function __construct($options)
    {
        $this->optionsAllowed($options);
        $this->options = $options;
    }

    protected function optionsAllowed($options) {
        $allowed = $this->allowedOptions();
        foreach($options as $key => $value) {
            if (!in_array($key, $allowed)) {
                throw new InvalidOptionException('The given option "' . $key . '" is not allowed in ' . get_class($this) . ' !');
            }
        }
    }

    protected function allowedOptions() {
        return array('property');
    }

    public function transform($value)
    {        //we don't know how the data is expected
        throw new TransformationFailedException('BaseTransformer: Can not transform data');
    }

    public function reverseTransform($value)
    {
        //empty filters should always be working on the other hand
        return array();
    }
}