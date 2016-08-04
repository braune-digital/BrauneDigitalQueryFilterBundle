<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;


use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

interface FilterInterface
{
    /**
     * @param QueryBuilderJoinWrapperInterface $qbWrapper
     * @param $manager
     * @param $alias
     * @param $property
     * @param $data
     * @return mixed
     */
    public function getExpr(QueryBuilderJoinWrapperInterface $qbWrapper, QueryManager $manager, $alias, $property, $data, $optional = false);
}