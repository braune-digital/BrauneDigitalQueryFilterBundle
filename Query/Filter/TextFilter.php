<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use Doctrine\ORM\Query\Expr\Comparison;
use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

class TextFilter extends BaseFilter
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
        $delimiter = array_key_exists('delimiter', $data) ? $data['delimiter'] : ' ';

        if ($delimiter) {
            $words = explode($delimiter, $data['text']);
        } else {
            $words = array($data['text']);
        }

        $mode = array_key_exists('mode', $data) ? $data['mode'] : 'or';
        $func = $mode != 'and' ? 'orX' : 'andX';


        $properties = array_key_exists('properties', $data) ? $data['properties'] : (array_key_exists('property', $data) ? array($data['property']) : null);

        $paths = [];
        if ($properties == null) {
            $paths[] = $alias . '.' . $property;
        } else {
            //convert paths
            foreach($properties as $property) {
                list($alias, $property) = $manager->getAliasProperty($qbWrapper, $property, true);
                $paths[] = $alias . '.' . $property;
            }
        }

        if (count($paths) == 0) {
            return null; //do not filter if properties is empty array
        }

        $expressions = array();

        //convert words into parameters
        foreach ($words as $word) {
            $wordParam = $qbWrapper->newParam('%'.$word.'%');
            //each word has to appear in at least one field
            $comparisons = array();
            foreach($paths as $path) {
                $comparison = new Comparison($path, 'LIKE', $wordParam);
                if ($comparison) {
                    $comparisons[] = $comparison;
                }
            }

            if (count($comparisons) > 0) {
                $expressions[] = call_user_func_array(array($qbWrapper->getQueryBuilder()->expr(), $func), $comparisons);
            }
        }


        if (count($expressions) > 0) {
            return call_user_func_array(array($qbWrapper->getQueryBuilder()->expr(), 'andX'), $expressions);
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
        //we dont care for alias and property
        //parent::checkData($alias, $property, $data);

        if (!array_key_exists('text', $data)) {
            throw new InvalidConfigException("TextFilter: invalid parameter for text");
        }

        if (!array_key_exists('properties', $data) && !array_key_exists('property', $data)) {
            throw new InvalidConfigException("TextFilter: invalid parameter for text");
        }
    }
}