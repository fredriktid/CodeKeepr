<?php

namespace Frigg\KeeprBundle\Elastica;

use Elastica\Filter;
use Elastica\Query;
use Elastica\Aggregation;
use Frigg\KeeprBundle\Elastica\Value\Aggregation as AggregationValue;

/**
 * Class QueryBuilder
 * @package Frigg\KeeprBundle\Elastica
 */
class QueryBuilder
{
    /**
     * @var Query $query
     */
    protected $query;

    /**
     * @var Query\BoolQuery
     */
    protected $boolQuery;

    /**
     * @var string
     */
    protected $queryString;

    /**
     * @var integer
     */
    protected $size;

    /**
     * @var array
     */
    protected $sortBy = [];

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
     * @var array
     */
    protected $aggregation = [];

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param Query\BoolQuery $boolQuery
     * @return QueryBuilder
     */
    public function setBoolQuery(Query\BoolQuery $boolQuery)
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
     * @param integer $size
     * @return QueryBuilder
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param mixed $sortBy
     * @return QueryBuilder
     */
    public function addSortBy($sortBy)
    {
        $this->sortBy[] = $sortBy;

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
     * @param string $type
     * @param AggregationValue $aggregation
     * @return QueryBuilder
     */
    public function addAggregation($type, AggregationValue $aggregation)
    {
        $this->aggregation[$type][] = $aggregation;

        return $this;
    }

    /**
     * Adds query string as must
     *
     * @return QueryBuilder
     */
    public function mustQueryString()
    {
        $this->boolQuery->addMust(
            new Query\QueryString($this->queryString)
        );

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
    public function mustMatchObjects()
    {
        foreach ($this->objects as $objectData) {
            list($objectField, $objectValue) = explode(':', $objectData);

            $this->boolQuery->addMust(
                new Query\Match($objectField, $objectValue)
            );
        }

        return $this;
    }

    /**
     * Build elastic query object
     *
     * @return Query
     */
    public function buildQuery()
    {
        $this->query = new Query($this->boolQuery);

        $this->applySize()
            ->applySortBy()
            ->aggregateOnTerms()
            ->aggregateOnNestedTerms()
            ->aggregateOnDateHistogram();

        return $this->query;
    }

    /**
     * @return QueryBuilder
     */
    protected function applySize()
    {
        $this->query->setSize($this->size);

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    protected function applySortBy()
    {
        foreach ($this->sortBy as $sortBy) {
            $this->query->addSort($sortBy);
        }

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    protected function aggregateOnTerms()
    {
        /** @var AggregationValue $aggObject */
        foreach ($this->aggregation['terms'] as $aggObject) {
            $termsAgg = new Aggregation\Terms($aggObject->getName());
            $termsAgg->setField($aggObject->getField());
            $termsAgg->setSize($aggObject->getSize());
            $this->query->addAggregation($termsAgg);
        }

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    protected function aggregateOnNestedTerms()
    {
        /** @var AggregationValue $aggObject */
        foreach ($this->aggregation['nested_terms'] as $aggObject) {
            $termsAgg = new Aggregation\Terms($aggObject->getName());
            $termsAgg->setField($aggObject->getField());
            $termsAgg->setSize($aggObject->getSize());

            $nestedAgg = new Aggregation\Nested($aggObject->getName(), $aggObject->getPath());
            $nestedAgg->addAggregation($termsAgg);
            $this->query->addAggregation($nestedAgg);
        }

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    protected function aggregateOnDateHistogram()
    {
        /** @var AggregationValue $aggObject */
        foreach ($this->aggregation['date_histogram'] as $aggObject) {
            $histogram = new Aggregation\DateHistogram(
                $aggObject->getName(),
                $aggObject->getField(),
                $aggObject->getInterval()
            );
            $histogram->setOrder('_count', 'desc');
            $this->query->addAggregation($histogram);
        }

        return $this;
    }
}
