<?php

namespace Frigg\KeeprBundle\Elastica;

use Elastica\Filter;
use Elastica\Query;

/**
 * Class QueryBuilder
 * @package Frigg\KeeprBundle\Elastica
 */
class QueryBuilder
{
    /**
     * @var Query\Bool
     */
    protected $boolQuery;

    /**
     * @var string
     */
    protected $queryString;

    /**
     * @var array
     */
    protected $nested = [];

    /**
     * @var array
     */
    protected $ranges = [];

    /**
     * @var array
     */
    protected $objects = [];

    /**
     * @return Query\Bool
     */
    public function getBoolQuery()
    {
        return $this->boolQuery;
    }

    /**
     * @param Query\Bool $boolQuery
     * @return QueryBuilder
     */
    public function setBoolQuery(Query\Bool $boolQuery)
    {
        $this->boolQuery = $boolQuery;

        return $this;
    }

    /**
     * @param string $queryString
     * @return QueryBuilder
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;

        return $this;
    }

    /**
     * @param array $nested
     * @return QueryBuilder
     */
    public function setNested(array $nested)
    {
        $this->nested = $nested;

        return $this;
    }

    /**
     * @param array $ranges
     * @return QueryBuilder
     */
    public function setRanges(array $ranges)
    {
        $this->ranges = $ranges;

        return $this;
    }

    /**
     * @param array $objects
     * @return QueryBuilder
     */
    public function setObjects(array $objects)
    {
        $this->objects = $objects;

        return $this;
    }

    /**
     * Adds query string as must
     *
     * @return QueryBuilder
     */
    public function mustQueryString()
    {
        $stringQuery = new Query\QueryString();
        $stringQuery->setQuery($this->queryString);
        $this->boolQuery->addMust($stringQuery);

        return $this;
    }

    /**
     * Filter on nested fields
     *
     * @return QueryBuilder
     */
    public function mustFilterNested()
    {
        foreach ($this->nested as $nestedData) {
            list($nestedField, $nestedValue) = array_map('trim', explode(':', $nestedData));
            $nestedPath = substr($nestedField, 0, strpos($nestedField, '.'));

            $matchQuery = new Query\Match();
            $matchQuery->setField($nestedField, $nestedValue);

            $boolQuery = new Query\BoolQuery();
            $boolQuery->addMust($matchQuery);

            $nestedQuery = new Query\Nested();
            $nestedQuery->setPath($nestedPath);
            $nestedQuery->setQuery($boolQuery);
            $this->boolQuery->addMust($nestedQuery);
        }

        return $this;
    }

    /**
     * Filter on range fields
     *
     * @return QueryBuilder
     */
    public function mustFilterRange()
    {
        $query = $this->boolQuery;

        foreach ($this->ranges as $rangeData) {
            $queryClone = clone $query;
            list($rangeField, $rangeOperator, $rangeDate) = explode(':', $rangeData);

            $query = new Query\Filtered($queryClone, new Filter\Range($rangeField, [
                $rangeOperator => $rangeDate
            ]));
        }

        $this->boolQuery = $query;

        return $this;
    }

    /**
     * Filter on object fields
     *
     * @return QueryBuilder
     */
    public function mustFilterObjects()
    {
        foreach ($this->objects as $objectData) {
            list($objectField, $objectValue) = explode(':', $objectData);

            $matchQuery = new Query\Match();
            $matchQuery->setField($objectField, $objectValue);
            $this->boolQuery->addMust($matchQuery);
        }

        return $this;
    }
}
