<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

class IsNotNullFilter extends BaseFilter
{
    /**
     * @param QueryBuilderJoinWrapperInterface $qbWrapper
     * @param QueryManager $manager
     * @param $alias
     * @param $property
     * @param $data
     * @return mixed
     */
    public function getExpr(QueryBuilderJoinWrapperInterface $qbWrapper, QueryManager $manager, $alias, $property, $data, $optional = false)
    {
        $this->checkData($alias, $property, $data);
        $path = $alias . "." . $property;
        return $qbWrapper->getQueryBuilder()->expr()->isNotNull($path);
    }

    /**
     * @param $alias
     * @param $property
     * @param $data
     */
    public function checkData($alias, $property, $data)
    {
        parent::checkData($alias, $property, $data);
    }
}