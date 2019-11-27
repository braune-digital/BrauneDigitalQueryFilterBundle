<?php

namespace BrauneDigital\QueryFilterBundle\Query;

use Doctrine\ORM\QueryBuilder;

interface QueryBuilderJoinWrapperInterface extends QueryBuilderParamWrapperInterface
{
    /**
     * @return null|string
     */
    function getFreeAlias();

    /**
     * @param $fullPath
     * @return string
     */
    function getAlias($fullPath, $optional = false);
}