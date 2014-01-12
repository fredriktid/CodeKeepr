<?php

namespace Frigg\KeeprBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;

abstract class ParentService
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

    final public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    final public function getEntity()
    {
        return $this->entity;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    abstract public function loadEntityById($id);

    public function getConfig($key)
    {
        return (array_key_exists($key, $this->config)) ? $this->config[$key] : null;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function getCollectionIds()
    {
        $collection = $this->getCollection();
        if (!is_array($collection) || !count($collection)) {
            return $collection;
        }

        $collectionIds = array();
        foreach ($collection as $item) {
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
