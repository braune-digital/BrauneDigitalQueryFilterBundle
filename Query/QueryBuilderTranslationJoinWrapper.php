<?php

namespace BrauneDigital\QueryFilterBundle\Query;

use BrauneDigital\QueryFilterBundle\Model\TranslatableInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class QueryBuilderTranslationJoinWrapper extends QueryBuilderJoinWrapper
{

    protected $aliasClassMap = array();
    protected $locale;

    /**
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb, $locale = null)
    {
        parent::__construct($qb);
        $this->locale = $locale;
        $this->aliasClassMap = array_combine($this->getQueryBuilder()->getRootAliases(), $this->getQueryBuilder()->getRootEntities());
    }

    /**
     * @param $alias
     * @return null
     */
    protected function getClass($alias)
    {

        if (isset($this->aliasClassMap[$alias])) {
            return $this->aliasClassMap[$alias];
        }
        return null;
    }

    /**
     * @param $alias
     * @return mixed
     */
    protected function isTranslatable($alias)
    {
        $metaData = $this->getMetadata($alias);

        return $metaData->reflClass->implementsInterface(TranslatableInterface::class);
    }

    /**
     * @param $alias
     * @return mixed
     */
    protected function getMetadata($alias)
    {
        return $this->getQueryBuilder()->getEntityManager()->getClassMetadata($this->getClass($alias));
    }

    /**
     * @param $alias
     * @param $property
     * @return bool
     */
    protected function hasProperty($alias, $property)
    {
        $metadata = $this->getMetadata($alias);
        return $metadata && ($metadata->hasAssociation($property) || $metadata->hasField($property));
    }

    /**
     * @param $rootAlias
     * @param $property
     * @return null|string
     */
    protected function join($rootAlias, $property, $optional = false)
    {
        $alias = $this->getFreeAlias();
        if (isset($this->joinedProperties[$property])) {
            return $this->joinedProperties[$property];
        }

        if ($optional) {
            $this->queryBuilder->leftJoin($rootAlias . '.' . $property, $alias);
        } else {
            $this->queryBuilder->join($rootAlias . '.' . $property, $alias);
        }

        $this->joinedProperties[$property] = $alias;

        $metadata = $this->getMetadata($rootAlias);
        $this->aliasClassMap[$alias] = $metadata->associationMappings[$property]['targetEntity'];

        return $alias;
    }

    protected function joinTranslations($rootAlias, $optional = false)
    {
        $alias = $this->getFreeAlias();

        if ($optional) {
            $this->queryBuilder->leftJoin($rootAlias . '.translations', $alias, Join::WITH, $alias . '.locale = ' . $this->newParam($this->locale));
        } else {
            $this->queryBuilder->join($rootAlias . '.translations', $alias, Join::WITH, $alias . '.locale = ' . $this->newParam($this->locale));
        }

        $metadata = $this->getMetadata($rootAlias);
        $this->aliasClassMap[$alias] = $metadata->associationMappings['translations']['targetEntity'];

        return $alias;
    }

    /**
     * @param $rootAlias
     * @param $property
     * @return bool
     * @throws \Exception
     */
    protected function getRelationAlias($rootAlias, $property, $optional = false)
    {

        $currentJoins = $this->queryBuilder->getDQLPart('join');

        $alias = false;
        if (isset($currentJoins[$rootAlias])) {
            foreach ($currentJoins[$rootAlias] as $join) {

                $joinPath = $join->getJoin();

                $pos = strpos($joinPath, ".");

                if ($pos === false) {
                    throw new \Exception("Invalid Joins");
                }

                if (strcmp(substr($joinPath, $pos + 1), $property) == 0) {
                    $alias = $join->getAlias();

                    //check if we have to make the join not optional
                    if (!$optional && $join->getJoinType() == Join::LEFT_JOIN) {
                        //yes we should
                        $reflectionClass = new \ReflectionClass(Join::class);
                        $reflectionProperty = $reflectionClass->getProperty('joinType');
                        $reflectionProperty->setAccessible(true);
                        $reflectionProperty->setValue($join, Join::INNER_JOIN);
                    }

                    break;
                }
            }
        }

        if ($alias) {
            //update classmap if missing
            if (!isset($this->aliasClassMap[$alias])) {
                $metadata = $this->getMetadata($rootAlias);
                $this->aliasClassMap[$alias] = $metadata->associationMappings[$property]['targetEntity'];
            }
        }

        return $alias;
    }


    /**
     * @param $fullPath
     * @return string
     */
    public function getAlias($fullPath, $optional = false)
    {
        //using reference

        $rootAlias = $this->queryBuilder->getRootAlias();

        $joins = explode('.', $fullPath);
        $size = count($joins);

        for ($i = 0; $i < $size - 1; $i++) {

            $alias = $this->getRelationAlias($rootAlias, $joins[$i], $optional);

            if ($alias == false) {
                if ($this->locale && !$this->hasProperty($rootAlias, $joins[$i]) && $this->isTranslatable($rootAlias)) {
                    $alias = $this->getRelationAlias($rootAlias, 'translations', $optional);
                    //translations were not joined yet
                    if ($alias == false) {
                        $alias = $this->joinTranslations($rootAlias, $optional);
                    }

                    $alias = $this->join($alias, $joins[$i], $optional);
                    //this is a translation, so we use the translation as a

                } else {
                    $alias = $this->join($rootAlias, $joins[$i], $optional);
                }
            }
            $rootAlias = $alias;
        }

        //check last element's property too
        if ($this->locale && !$this->hasProperty($rootAlias, $joins[$size - 1])) {
            if ($this->isTranslatable($rootAlias)) {
                $alias = $this->getRelationAlias($rootAlias, 'translations', $optional);

                //translations were not joined yet
                if ($alias == false) {
                    $alias = $this->joinTranslations($rootAlias, $optional);
                }

                $rootAlias = $alias;
                //this is a translation, so we use the translation as a base
            }
        }

        return $rootAlias;
    }

}