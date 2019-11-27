<?php

namespace BrauneDigital\QueryFilterBundle\Query;

interface QueryBuilderParamWrapperInterface
{

    /**
     * @return mixed
     */
    function getQueryBuilder();

    /**
     * @param $value
     * @return string
     */
    function newParam($value);
}