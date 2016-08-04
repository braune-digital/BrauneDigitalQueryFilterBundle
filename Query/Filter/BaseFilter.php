<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

abstract class BaseFilter implements FilterInterface
{

    /**
     * @param QueryBuilderJoinWrapperInterface $qbWrapper
     * @param $manager
     * @param $alias
     * @param $property
     * @param $data
     * @return mixed
     */
    public function getExpr(QueryBuilderJoinWrapperInterface $qbWrapper, QueryManager $manager, $alias, $property, $data, $optional = false)
    {
        return null;
    }

    /**
     * @param $alias
     * @param $property
     * @param $data
     */
    protected function checkData($alias, $property, $data)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $alias)) {
            throw new InvalidConfigException('Alias contains special characters!');
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $property)) {
            throw new InvalidConfigException('Property contains special characters!');
        }
    }
}