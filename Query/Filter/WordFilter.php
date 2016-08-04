<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use Doctrine\ORM\Query\Expr\Comparison;
use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

class WordFilter extends BaseFilter
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

        $delimiter = array_key_exists('delimiter', $data) ? $data['delimiter'] : ' ';
        $mode = array_key_exists('mode', $data) ? $data['mode'] : 'or';

        $words = explode($delimiter, $data['value']);

        $expressions = array();
        foreach($words as $word) {
            $comparison = new Comparison($path, 'LIKE', $qbWrapper->newParam('%'.$word.'%'));
            if ($comparison) {
                $expressions[] = $comparison;
            }
        }

        if (count($expressions) > 0) {
            $func = $mode == 'or' ? 'orX' : 'andX';
            return call_user_func_array(array($qbWrapper->getQueryBuilder()->expr(), $func), $expressions);
        }
        return null;
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
            throw new InvalidConfigException("WordFilter: invalid parameter for value");
        }
    }
}