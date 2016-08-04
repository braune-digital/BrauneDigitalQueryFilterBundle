<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use Doctrine\ORM\Query;
use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

/**
 * collects different filters
 * Class GroupFilter
 * @package BrauneDigital\QueryFilterBundle\Filter
 */
class GroupFilter extends BaseFilter
{

    protected $mode;
    public function __construct($mode = null) {
        $this->mode = $mode;
    }

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

        $qb = $qbWrapper->getQueryBuilder();

        $and = null;
        $or = null;

        if ($this->mode == 'and') {
            $and =  array_key_exists('filters', $data) ? $this->getExpressions($qbWrapper, $manager, $alias, $property, $data['filters'], true) : null;
        } else if ($this->mode == 'or') {
            $or =  array_key_exists('filters', $data) ? $this->getExpressions($qbWrapper, $manager, $alias, $property, $data['filters'], false) : null;
        } else {
            $and =  array_key_exists('and', $data) ? $this->getExpressions($qbWrapper, $manager, $alias, $property, $data['and'], true) : null;
            $or =  array_key_exists('or', $data) ? $this->getExpressions($qbWrapper, $manager, $alias, $property, $data['or'], false) : null;
        }

        if ($or) {
            $or = call_user_func_array(array($qb->expr(), 'orX'), $or);
        }

        if ($and) {
            $and = call_user_func_array(array($qb->expr(), 'andX'), $and);
        }

        if ($and && $or) {
            return $qb->expr()->andX($and, $or);
        } else if ($and) {
            return $and;
        } else if ($or) {
            return $or;
        }

        return parent::getExpr($qbWrapper, $manager, $alias, $property, $data);
    }

    protected function getExpressions(QueryBuilderJoinWrapperInterface $qbWrapper, QueryManager $manager, $alias, $property, $filters, $optional) {
        $expressions = array();

        foreach ($filters as $filterConfig) {
            $expr = $manager->getExpr($qbWrapper, $filterConfig, $alias, $property, $optional);
            if ($expr != null) {
                $expressions[] = $expr;
            }
        }

        if (count($expressions) > 0) {
            return $expressions;
        } else {
            return null;
        }
    }

    /**
     * @return mixed
     */
    protected function checkData($alias, $property, $data)
    {
        if ($alias && $property) {
            parent::checkData($alias, $property, $data);
        }
        if (!is_array($data)) {
            throw new InvalidConfigException("Invalid config for group filter given.");
        }
        //the filters are already checking themselves so we dont have to check again
    }
}