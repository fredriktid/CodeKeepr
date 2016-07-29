<?php

namespace Frigg\KeeprBundle\Value;

/**
 * Class AbstractValue
 * @package Frigg\KeeprBundle\Value
 */
abstract class AbstractValue
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * AbstractValue constructor.
     * @param array|null $values
     */
    public function __construct(array $values = null)
    {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $this->setValue($key, $value);
            }
        }
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->getValue($key);
    }

    /**
     * @param $key
     * @param $value
     * @return AbstractValue
     */
    public function __set($key, $value)
    {
        return $this->setValue($key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setValue($key, $value)
    {
        $this->values[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getValue($key)
    {
        return (array_key_exists($key, $this->values)) ? $this->values[$key] : null;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (empty($this->values));
    }
}
