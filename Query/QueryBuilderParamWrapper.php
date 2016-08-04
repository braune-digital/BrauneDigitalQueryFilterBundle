<?php

namespace BrauneDigital\QueryFilterBundle\Query;

use Doctrine\ORM\QueryBuilder;

class QueryBuilderParamWrapper implements QueryBuilderParamWrapperInterface
{
    protected $queryBuilder;
    protected $paramCounter;
    protected $type;

    const TYPE_NUMBERED = 0;
    const TYPE_NAMED = 1;

    /**
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->queryBuilder = $qb;
        $this->paramCounter = 0;

        $array = $qb->getParameters()->toArray();
        if (array_values($array) !== $array) {
            $this->type = QueryBuilderParamWrapper::TYPE_NAMED;
        } else {
            $this->type = QueryBuilderParamWrapper::TYPE_NUMBERED;
        }
    }

    /**
     * @return mixed
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param $value
     * @return string
     */
    public function newParam($value)
    {

        if ($this->type == QueryBuilderParamWrapper::TYPE_NAMED) {

            $prefix = ":";
            $key = null;

            do {
                $key = "filter_param_" . $this->paramCounter;
                $this->paramCounter++;
            } while ($this->queryBuilder->getParameters()->containsKey($key));

        } else {
            $prefix = "?";
            $key = $this->queryBuilder->getParameters()->count();
        }

        //generate unique param key
        $this->queryBuilder->setParameter($key, $value);
        return $prefix . $key;
    }
}