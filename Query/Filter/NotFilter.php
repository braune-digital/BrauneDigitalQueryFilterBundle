<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

class NotFilter extends BaseFilter
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
        if (!empty($data['expr'])) {
            //make optional
            $optional = !isset($data['required']) || !$data['required'];
            $expr = $manager->getExpr($qbWrapper, $data['expr'], $alias, $property, $optional);
            if ($expr) {
                $expr = 'NOT (' . $expr . ')';
                return $expr;
            }
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
        //parent::checkData($alias, $property, $data);
    }
}