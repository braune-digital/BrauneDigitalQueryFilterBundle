<?php

namespace BrauneDigital\QueryFilterBundle\Query;

use BrauneDigital\QueryFilterBundle\Exception\PathInhibitedException;
use BrauneDigital\QueryFilterBundle\Query\InhibitorConfig\InhibitorConfigInterface;

class QueryBuilderJoinInhibitorDecorator implements QueryBuilderJoinWrapperInterface
{

    /** @var QueryBuilderJoinWrapperInterface */
    protected $decorated;
    /**
     * @var InhibitorConfigInterface
     */
    protected $inhibtorConfig;

    /**
     * QueryBuilderJoinInhibitorDecorator constructor.
     * @param QueryBuilderJoinWrapperInterface $decorated
     * @param InhibitorConfigInterface $inhibtorConfig
     */
    public function __construct(QueryBuilderJoinWrapperInterface $decorated, InhibitorConfigInterface $inhibtorConfig)
    {
        $this->decorated = $decorated;
        $this->inhibtorConfig = $inhibtorConfig;
    }

    /**
     * @return QueryBuilderJoinWrapperInterface
     */
    public function getDecorated(): QueryBuilderJoinWrapperInterface
    {
        return $this->decorated;
    }

    /**
     * @return InhibitorConfigInterface
     */
    public function getInhibtorConfig(): InhibitorConfigInterface
    {
        return $this->inhibtorConfig;
    }


    public function getFreeAlias()
    {
        return $this->decorated->getFreeAlias();
    }

    public function getQueryBuilder()
    {
        return $this->decorated->getQueryBuilder();
    }

    public function newParam($value)
    {
        return $this->decorated->newParam($value);
    }


    public function getAlias($fullPath, $optional = false)
    {
        if ($this->inhibtorConfig->isPathInhibited($fullPath)) {
            throw new PathInhibitedException($this, $fullPath);
        }

        return $this->decorated->getAlias($fullPath, $optional);
    }
}