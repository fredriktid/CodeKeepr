<?php

namespace Frigg\KeeprBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

/**
 * Class ActivityListener
 * @package Frigg\KeeprBundle\EventListener
 */
class ActivityListener
{
    /**
     * @var null|ContainerInterface
     */
    protected $container = null;
    /**
     * @var null
     */
    protected $entity = null;
    /**
     * @var array
     */
    protected $validEntities = [
        'Post'
    ];

    /**
     * ActivityListener constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return bool
     */
    protected function isValidEntity()
    {
        if (!is_object($this->entity)) {
            return false;
        }

        $classChunks = explode('\\', get_class($this->entity));
        return in_array(end($classChunks), $this->validEntities);
    }

    /**
     * @param LifecycleEventArgs $arguments
     */
    public function postPersist(LifecycleEventArgs $arguments)
    {
        $this->setEntity($arguments->getEntity());

        if (!$this->isValidEntity()) {
            return;
        }

        $this->persist('setIdentifier', $this->entity->sanitize($this->entity->getTopic()));
    }

    /**
     * @param LifecycleEventArgs $arguments
     */
    public function postUpdate(LifecycleEventArgs $arguments)
    {
        $this->setEntity($arguments->getEntity());
        if (!$this->isValidEntity()) {
            return;
        }

        $this->persist('setIdentifier', $this->entity->sanitize($this->entity->getTopic()));
    }

    /**
     * @param $method
     * @param $identifier
     */
    protected function persist($method, $identifier)
    {
        if (!method_exists($this->entity, $method)) {
            return;
        }

        call_user_func([$this->entity, $method], $identifier);

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($this->entity);
        $em->flush();
    }
}
