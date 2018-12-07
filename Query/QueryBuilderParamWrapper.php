<?php

namespace BrauneDigital\QueryFilterBundle\Query;

use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;

class QueryBuilderParamWrapper implements QueryBuilderParamWrapperInterface
{
    protected $queryBuilder;
    protected $paramCounter;
    protected $type;
    protected $paramPrefix;

    const TYPE_NUMBERED = 0;
    const TYPE_NAMED = 1;

    /**
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb, $paramPrefix = "")
    {
        $this->queryBuilder = $qb;
        $this->paramCounter = 0;
        $this->paramPrefix = $paramPrefix;

        $array = $qb->getParameters()->toArray();
        if (array_values($array) !== $array) {
            $this->type = QueryBuilderParamWrapper::TYPE_NAMED;
        } else {
            $this->type = QueryBuilderParamWrapper::TYPE_NUMBERED;
        }
    }

    public function getparamPrefix() {
        return $this->paramPrefix;
    }

    public function addParamOffset($offset) {
        $this->paramCounter += (int) $offset;
    }

    public function getParamCounter() {
        return $this->paramCounter;
    }

    public function copyParams(QueryBuilderParamWrapperInterface $paramWrapper) {
        $otherQb = $paramWrapper->getQueryBuilder();
        foreach ($otherQb->getParameters() as $parameter) {
            /** @var Parameter $parameter */
            $this->queryBuilder->setParameter($parameter->getName(), $parameter->getValue());
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
                $key = $this->paramPrefix . "filter_param_" . $this->paramCounter;
                $this->paramCounter++;
            } while ($this->queryBuilder->getParameters()->containsKey($key));
        } else {
            $prefix = "?";
            $this->paramCounter = max($this->paramCounter, $this->queryBuilder->getParameters()->count());
            $key = $this->paramCounter++;
        }
        //generate unique param key
        $this->queryBuilder->setParameter($key, $value);
        return $prefix . $key;
    }
}