<?php

namespace BrauneDigital\QueryFilterBundle\Service;

use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\Filter\FilterInterface;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderTranslationJoinWrapper;
use Doctrine\ORM\QueryBuilder;

class QueryManager
{
    protected $filters;

    public function __construct()
    {
        $this->filters = [];
    }

    public function addFilter(FilterInterface $filter, $alias)
    {
        $this->filters[$alias] = $filter;
    }

    public static function removeSpecialCharacters(string $string) : ?string
    {
        return preg_replace('/[^A-Za-z0-9\-_\.]/', '', $string);
    }
    /**
     * @return FilterInterface
     */
    protected function getFilter($alias)
    {
        if (array_key_exists($alias, $this->filters)) {
            return $this->filters[$alias];
        } else {
            throw new \Exception('Filter '.$alias.' not found.');
        }
    }

    public function getAliasProperty(QueryBuilderJoinWrapperInterface $qbWrapper, $path, $optional = false)
    {
        $path = $this->toCamelCase($path);
        $path = self::removeSpecialCharacters($path);

        $alias = $qbWrapper->getAlias($path, $optional);

        $pos = strrpos($path, '.');

        if ($pos !== false) {
            $property = substr($path, $pos + 1);
        } else {
            $property = $path;
        }

        return [$alias, $property];
    }

    public function getExpr(QueryBuilderJoinWrapperInterface $qbWrapper, $data, $alias = null, $property = null, $optional = false)
    {
        if (empty($data)) {
            return null;
        }

        if (array_key_exists('property', $data)) {
            [$alias, $property] = $this->getAliasProperty($qbWrapper, $data['property'], $optional);
        }

        $filterType = null;
        if (array_key_exists('type', $data)) {
            $filterType = $data['type'];
        } elseif (array_key_exists('filter', $data)) {
            $filterType = $data['filter'];
        } else {
            throw new \Exception('No filter specified in '.$alias.'.'.$property);
        }

        $filter = $this->getFilter($filterType);

        return $filter->getExpr($qbWrapper, $this, $alias, $property, $data, $optional);
    }

    /**
     * @param array $filterConfig
     * @param null  $locale
     *
     * @deprecated
     */
    public function filter(QueryBuilder $queryBuilder, $filterConfig = [], $locale = null)
    {
        $qbWrapper = new QueryBuilderTranslationJoinWrapper($queryBuilder, $locale);
        $this->filterWithWrapperOnly($qbWrapper, $filterConfig);
    }

    public function filterWithWrapperOnly(QueryBuilderJoinWrapperInterface $qbWrapper, $filterConfig = [])
    {
        $this->filterWithWrapper($qbWrapper->getQueryBuilder(), $qbWrapper, $filterConfig);
    }

    /**
     * @param array $filterConfig
     *
     * @throws InvalidConfigException
     *
     * @deprecated
     */
    public function filterWithWrapper(QueryBuilder $queryBuilder, QueryBuilderJoinWrapperInterface $qbWrapper, $filterConfig = [])
    {
        if ($filterConfig === false) {
            return; // NOOP
        }

        if (!is_array($filterConfig)) {
            throw new InvalidConfigException('The filter config must be an array.');
        }

        if (count($filterConfig) == 0) {
            // NOOP
            return;
        }

        if (array_keys($filterConfig) === range(0, count($filterConfig) - 1)) {
            // build filters
            foreach ($filterConfig as $property => $filterData) {
                // $property = self::removeSpecialChars($property);
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
     * @param array $orderConfig
     * @param null  $locale
     */
    public function order(QueryBuilder $queryBuilder, $orderConfig = [], $locale = null)
    {
        $qbWrapper = new QueryBuilderTranslationJoinWrapper($queryBuilder, $locale);
        $this->orderWithWrapper($queryBuilder, $qbWrapper, $orderConfig);
    }

    /**
     * @param array $orderConfig
     */
    public function orderWithWrapper(QueryBuilder $queryBuilder, QueryBuilderJoinWrapperInterface $qbWrapper, $orderConfig = [])
    {
        if (!is_array($orderConfig)) {
            throw new InvalidConfigException('The order config must be an array.');
        }

        foreach ($orderConfig as $path => $order) {
            if (!is_string($path)) {
                throw new InvalidConfigException('The order config must be an array of valid paths');
            }

            $path = $this->toCamelCase($path);
            $path = self::removeSpecialCharacters($path);

            $pos = strrpos($path, '.');

            if ($pos !== false) {
                $property = substr($path, $pos + 1);
            } else {
                $property = $path;
            }

            $order = strtoupper($order);
            if (in_array($order, ['ASC', 'DESC'])) {
                $qbWrapper->getQueryBuilder()->addOrderBy($qbWrapper->getAlias($path, true).'.'.$property, $order);
            } else {
                throw new InvalidConfigException('Invalid sortBy Value for order'.$path);
            }
        }
    }

    /**
     * @param $str
     *             TODO: This is not the best place here :D
     */
    public function toCamelCase($str)
    {
        $parts = explode('_', strtolower($str));
        $size = count($parts);
        $str = $parts[0];
        for ($i = 1; $i < $size; ++$i) {
            $str .= ucfirst(trim($parts[$i]));
        }

        return $str;
    }
}
