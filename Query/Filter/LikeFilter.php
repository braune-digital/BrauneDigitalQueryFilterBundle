<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use Doctrine\ORM\Query\Expr\Comparison;
use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

class LikeFilter extends BaseFilter
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

        $comparison = new Comparison($path, 'LIKE', $qbWrapper->newParam($data['value']));
        return $comparison;
    }

    /**
     * @param $alias
     * @param $property
     * @param $data
     */
    public function checkData($alias, $property, $data)
    {
        parent::checkData($alias, $property, $data);

        if (!array_key_exists('value', $data)) {
            throw new InvalidConfigException("LikeFilter: invalid parameter for value");
        }
    }
}