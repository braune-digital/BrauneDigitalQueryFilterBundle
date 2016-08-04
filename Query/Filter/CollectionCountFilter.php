<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

class CollectionCountFilter extends BaseFilter
{

    /**
     * @param QueryBuilderJoinWrapperInterface $qbWrapper
     * @param QueryManager $manager
     * @param $alias
     * @param $property
     * @param $data
     * @return bool|mixed
     */
    public function getExpr(QueryBuilderJoinWrapperInterface $qbWrapper, QueryManager $manager, $alias, $property, $data, $optional = false)
    {
        $this->checkData($alias, $property, $data);

        $qb = $qbWrapper->getQueryBuilder();

        $basePath = $qbWrapper->getPath($alias);

        if (strlen($basePath) > 0) {
            $basePath .= '.';
        }

        $alias = $qbWrapper->getAlias($basePath . $property . '.id', $optional);

        $groupBy = false;

        if (array_key_exists('min', $data)) {
            $qb->andHaving(
                $qb->expr()->gte(
                    $qb->expr()->count($alias), $qbWrapper->newParam($data['min'])
                )
            );
            $groupBy = true;

        }

        if (array_key_exists('max', $data)) {
            $qb->andHaving(
                $qb->expr()->lte(
                    $qb->expr()->count($alias), $qbWrapper->newParam($data['max'])
                )
            );
            $groupBy = true;
        }

        if ($groupBy) {
            $qb->addGroupBy($qbWrapper->getRootAlias(). '.id');
        }

        return parent::getExpr($qbWrapper, $manager, $alias, $property, $data, $optional);
    }

    /**
     * @param $alias
     * @param $property
     * @param $data
     */
    protected function checkData($alias, $property, $data)
    {
        parent::checkData($alias, $property, $data);
        if (!array_key_exists('min', $data) && !array_key_exists('max', $data)) {
            throw new InvalidConfigException("RangeFilter: invalid parameter for min and max");
        }
    }
}