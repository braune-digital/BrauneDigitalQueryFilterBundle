<?php
namespace BrauneDigital\QueryFilterBundle\Query\Filter;

use BrauneDigital\QueryFilterBundle\Exception\InvalidConfigException;
use BrauneDigital\QueryFilterBundle\Query\QueryBuilderJoinWrapperInterface;
use BrauneDigital\QueryFilterBundle\Service\QueryManager;

class DateTimeRangeFilter extends BaseFilter
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

        $min = false;
        $max = false;

        $path = $alias . "." . $property;

        if (array_key_exists('min', $data)) {
            $min = $qb->expr()->gte($path, $qbWrapper->newParam($this->toDate($data['min'])));
        }

        if (array_key_exists('max', $data)) {
            $max = $qb->expr()->lte($path, $qbWrapper->newParam($this->toDate($data['max'])));
        }

        if ($min && $max) {
            return $qb->expr()->andX($min, $max);
        } else if ($min) {
            return $min;
        } else if ($max) {
            return $max;
        }

        return parent::getExpr($qbWrapper, $manager, $alias, $property, $data);
    }


    protected function toDate($string) {
        $tz = date_default_timezone_get();
        $dateTime = new \DateTime($string);

        if ($tz !== $dateTime->getTimezone()->getName()) {
            $dateTime->setTimezone(new \DateTimeZone($tz));
        }
        return $dateTime;
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
            throw new InvalidConfigException("DateRangeFilter: invalid parameter for min and max");
        }
    }
}