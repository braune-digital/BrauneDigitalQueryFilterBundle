<?php

namespace BrauneDigital\QueryFilterBundle\Query;

use Doctrine\ORM\QueryBuilder;

interface QueryBuilderParamWrapperInterface
{
    /**
     * @param QueryBuilder $qb
     */
    function __construct(QueryBuilder $qb);


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