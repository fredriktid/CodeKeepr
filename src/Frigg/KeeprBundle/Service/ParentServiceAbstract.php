<?php

namespace Frigg\KeeprBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;

abstract class ParentServiceAbstract
{
    protected $em;
    protected $config;
    protected $entity = null;
    protected $collection = array();

    public function __construct(EntityManager $em, $configFile)
    {
        $this->em = $em;
        $this->config = Yaml::parse(file_get_contents($configFile));
    }

    public function getConfig($key)
    {
        return (array_key_exists($key, $this->config)) ? $this->config[$key] : null;
    }

    final public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    final public function getEntity()
    {
        return $this->entity;
    }

    abstract public function loadEntityById($id);

    public function getEntityManager()
    {
        return $this->em;
    }


    public function getLoadedCollection()
    {
        return $this->collection;
    }

    public function getLoadedCollectionIds()
    {
        if (!is_array($this->collection) || !count($this->collection)) {
            return $this->collection;
        }

        $collectionIds = array();
        foreach ($this->collection as $item) {
            $collectionIds[] = $item->getId();
        }

        return $collectionIds;
    }

    final public static function arraySearchRecursive($needle, $haystack, $strict = false, $path = array())
    {
        if (!is_array($haystack)) {
            return false;
        }

        foreach ($haystack as $key => $val) {
            if (is_array($val) && $subPath = static::arraySearchRecursive($needle, $val, $strict, $path)) {
                $path = array_merge($path, array($key), $subPath);
                return $path;
            } elseif ((!$strict && $val == $needle) || ($strict && $val === $needle)) {
                $path[] = $key;
                return $path;
            }
        }

        return false;
    }
}
