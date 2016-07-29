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
        $field = $this->getValue('field');

        if ($separator = strpos($field, '.')) {
            return substr($field, 0, $separator);

        }

        return $field;
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
        return (int) $this->getValue('size');
    }

    /**
     * @return mixed|null
     */
    public function getField()
    {
        return $this->getValue('field');
    }

    /**
     * @return mixed|null
     */
    public function getInterval()
    {
        return $this->getValue('interval');
    }
}
