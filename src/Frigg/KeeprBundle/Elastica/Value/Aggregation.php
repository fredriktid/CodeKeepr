<?php

namespace Frigg\KeeprBundle\Elastica\Value;

use Frigg\KeeprBundle\Value\AbstractValue;

/**
 * Class Aggregation
 * @package Frigg\KeeprBundle\Elastica\Value
 */
class Aggregation extends AbstractValue
{
    /**
     * @return string
     */
    public function getPath()
    {
        $field = $this->getData('field');

        return substr($field, 0, strpos($field, '.'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        $path = $this->getPath();

        return ucfirst($path);
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return (int) $this->getData('size');
    }

    /**
     * @return mixed|null
     */
    public function getField()
    {
        return $this->getData('field');
    }
}
