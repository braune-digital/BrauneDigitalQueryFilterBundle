<?php

namespace BrauneDigital\QueryFilterBundle\Query;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class QueryBuilderJoinWrapper extends QueryBuilderParamWrapper implements QueryBuilderJoinWrapperInterface
{

    protected $joinCounter;

    protected $joinedProperties = array();

    /**
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        parent::__construct($qb);
        $this->joinCounter = 0;
    }

    /**
     * @return null|string
     */
    public function getFreeAlias()
    {
        $aliases = $this->queryBuilder->getAllAliases();

        $aliasWithCount = null;

        do {
            $aliasWithCount = "a" . $this->joinCounter;
            $this->joinCounter++;
        } while (in_array($aliasWithCount, $aliases));

        return $aliasWithCount;
    }

    /**
     * get the root alias, copied from QueryBuilder since it is deprecated there
     */
    public function getRootAlias() {
        $aliases = $this->queryBuilder->getRootAliases();

        if ( ! isset($aliases[0])) {
            throw new \RuntimeException('No alias was set before invoking getRootAlias().');
        }

        return $aliases[0];
    }

    /**
     * @param $fullPath
     * @return string
     */
    public function getAlias($fullPath, $optional = false)
    {
        //using reference
        $rootAlias = $this->getRootAlias();

        $joins = explode('.', $fullPath);
        $size = count($joins);

        for ($i = 0; $i < $size - 1; $i++) {

            $currentJoins = $this->queryBuilder->getDQLPart('join');

            $alias = false;

            if (isset($currentJoins[$rootAlias])) {
                foreach ($currentJoins[$rootAlias] as $k => $join) {

                    $joinPath = $join->getJoin();

                    $pos = strpos($joinPath, ".");

                    if ($pos === false) {
                        throw new \Exception("Invalid Joins");
                    }

                    if (strcmp(substr($joinPath, $pos + 1), $joins[$i]) == 0) {
                        $alias = $join->getAlias();

                        //check if we have to make the join not optional
                        if (!$optional && $join->getJoinType() == Join::LEFT_JOIN) {
                            //yes we should
                            $reflectionClass = new \ReflectionClass(Join::class);
                            $reflectionProperty = $reflectionClass->getProperty('joinType');
                            $reflectionProperty->setAccessible(true);
                            $reflectionProperty->setValue($join, Join::INNER_JOIN);
                        }
                        break;
                    }
                }
            }

            if ($alias == false) {
                $alias = $this->getFreeAlias();

                if ($optional) {
                    $this->queryBuilder->leftJoin($rootAlias . '.' . $joins[$i], $alias);
                } else {
                    $this->queryBuilder->join($rootAlias . '.' . $joins[$i], $alias);
                }
                //make a join
            }
            $rootAlias = $alias;
        }
        return $rootAlias;
    }

    /**
     * @param $alias
     *
     * @return string
     */
    public function getPath($alias) {
        $currentJoins = $this->queryBuilder->getDQLPart('join');
        foreach($currentJoins as $rootAlias => $joins) {
            foreach($joins as $join) {
                if ($join->getAlias() == $alias) {
                    $property = explode('.', $join->getJoin())[1];
                    $path = $this->getPath($rootAlias) . '.' . $property;
                    return substr($path, 1);
                }
            }
        }
        return "";
    }
}