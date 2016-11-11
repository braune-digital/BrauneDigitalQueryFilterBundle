<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

class EqualFilter extends BaseFilter
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


        if (array_key_exists('value', $data)) {
            return $qbWrapper->getQueryBuilder()->expr()->eq($path, $qbWrapper->newParam($data['value']));
        } else {
            //multiple values
            $values = $data['values'];

            if(!is_array($values)) {
                $values = array($values); //support old submit
            }

            $expressions = [];
            foreach($values as $value) {
                $expressions[] = $qbWrapper->getQueryBuilder()->expr()->eq($path, $qbWrapper->newParam($value));
            }

            if (count($expressions)) {
                return call_user_func_array(array($qbWrapper->getQueryBuilder()->expr(), 'orX'), $expressions);
            } else {
                return null;
            }
        }
    }

    /**
     * @param $alias
     * @param $property
     * @param $data
     */
    public function checkData($alias, $property, $data)
    {

        parent::checkData($alias, $property, $data);

        if (!array_key_exists('value', $data) && !array_key_exists('values', $data)) {
            throw new InvalidConfigException("EqualFilter: invalid parameter for value");
        }
    }
}
