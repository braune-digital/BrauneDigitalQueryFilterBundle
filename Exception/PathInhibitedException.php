<?php
namespace BrauneDigital\QueryFilterBundle\Exception;


use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinInhibitorDecorator;

class PathInhibitedException extends \Exception
{
    /**
     * @var QueryBuilderJoinInhibitorDecorator
     */
    protected $wrapper;


    /**
     * @var string
     */
    protected $path;

    /**
     * PathInhibitedException constructor.
     * @param QueryBuilderJoinInhibitorDecorator $wrapper
     * @param string $path
     */
    public function __construct(QueryBuilderJoinInhibitorDecorator $wrapper, $path)
    {
        $this->wrapper = $wrapper;
        $this->path = $path;
        parent::__construct(
            "Path " . $path . " was requested, but is not allowed by inhibtorConfig",
            401
        );
    }
}