<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

class NotInFilter extends BaseFilter
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

        return $qbWrapper->getQueryBuilder()->expr()->notIn($path, $qbWrapper->newParam($data['values']));
    }

    /**
     * @param $alias
     * @param $property
     * @param $data
     */
    public function checkData($alias, $property, $data)
    {
        parent::checkData($alias, $property, $data);

        if (!array_key_exists('values', $data)) {
            throw new InvalidConfigException("EqualFilter: invalid parameter for values");
        }
    }
}