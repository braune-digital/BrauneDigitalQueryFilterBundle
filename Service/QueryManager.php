<?php

namespace BrauneDigital\QueryFilterBundle\Service;

use Doctrine\ORM\QueryBuilder;
use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\Filter\FilterInterface;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderTranslationJoinWrapper;

class QueryManager {

    protected $filters;

    /**
     *
     */
    public function __construct()
    {
        $this->filters = array();
    }

    /**
     * @param FilterInterface $filter
     * @param $alias
     */
    public function addFilter(FilterInterface $filter, $alias)
    {
        $this->filters[$alias] = $filter;
    }

    /**
     * @param $alias
     * @return FilterInterface
     */
    protected function getFilter($alias)
    {
        if (array_key_exists($alias, $this->filters)) {
            return $this->filters[$alias];
        } else {
            throw new \Exception('Filter ' . $alias . " not found.");
        }
    }

    public function getAliasProperty(QueryBuilderJoinWrapperInterface $qbWrapper, $path, $optional = false) {
        $path = $this->toCamelCase($path);

        $alias = $qbWrapper->getAlias($path, $optional);

        $pos = strrpos($path, '.');

        if($pos !== false) {
            $property = substr($path, $pos + 1);
        } else {
            $property = $path;
        }
        return array($alias, $property);
    }

    public function getExpr(QueryBuilderJoinWrapperInterface $qbWrapper, $data, $alias = null, $property = null, $optional = false) {

        if (array_key_exists('property', $data)) {
            list($alias, $property) = $this->getAliasProperty($qbWrapper, $data['property'], $optional);
        }

        $filterType = null;
        if (array_key_exists('filter', $data)) {
            $filterType = $data['filter'];
        } else {
            throw new \Exception('No filter specified in ' . $alias . '.' . $property);
        }

        $filter = $this->getFilter($filterType);

        return $filter->getExpr($qbWrapper, $this, $alias, $property, $data, $optional);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $filterConfig
     * @param null $locale
     */
    public function filter(QueryBuilder $queryBuilder, $filterConfig = array(), $locale = null) {

        if (!is_array($filterConfig)) {
            throw new InvalidConfigException('The filter config must be an array.');
        }

        if (count($filterConfig) == 0) {
            //NOOP
            return;
        }

        $qbWrapper = new QueryBuilderTranslationJoinWrapper($queryBuilder, $locale);

        if (array_keys($filterConfig) === range(0, count($filterConfig) - 1)) {

            //build filters
            foreach($filterConfig as $property => $filterData) {

                //convert old style
                if (!array_key_exists('property', $filterData)) {
                    $filterData['property'] = $property;
                }

                $expr = $this->getExpr($qbWrapper, $filterData);

                if ($expr != null) {
                    $queryBuilder->andWhere($expr);
                }
            }
        } else {
            $expr = $this->getExpr($qbWrapper, $filterConfig);

            if ($expr != null) {
                $queryBuilder->andWhere($expr);
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $orderConfig
     * @param null $locale
     */
    public function order(QueryBuilder $queryBuilder, $orderConfig = array(), $locale = null) {

        if (!is_array($orderConfig)) {
            throw new InvalidConfigException('The order config must be an array.');
        }

        $qbWrapper = new QueryBuilderTranslationJoinWrapper($queryBuilder, $locale);

        foreach($orderConfig as $path => $order) {

            if(!is_string($path)) {
                throw new InvalidConfigException('The order config must be an array of valid paths');
            }

            $path = $this->toCamelCase($path);

            $pos = strrpos($path, '.');

            if($pos !== false) {
                $property = substr($path, $pos + 1);
            } else {
                $property = $path;
            }

            if(strcasecmp($order, "ASC") || strcasecmp($order, "DESC")) {
                $qbWrapper->getQueryBuilder()->addOrderBy($qbWrapper->getAlias($path, true) . "." . $property, $order);
            } else {
                throw new InvalidConfigException("Invalid sortBy Value for order" . $path);
            }
        }
    }

    /**
     * @param $str
     */
    public function toCamelCase($str) {
        $parts = explode('_', strtolower($str));
        $size = count($parts);
        $str = $parts[0];
        for($i = 1; $i < $size; $i++) {
            $str .= ucfirst(trim($parts[$i]));
        }
        return $str;
    }
}