<?php

namespace Frigg\KeeprBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

class ActivityListener
{
    protected $container = null;
    protected $entity = null;
    protected $validEntities = [
        'Post'
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    protected function isValidEntity()
    {
        if (!is_object($this->entity)) {
            return false;
        }

        $classChunks = explode('\\', get_class($this->entity));
        return in_array(end($classChunks), $this->validEntities);
    }

    public function postPersist(LifecycleEventArgs $arguments)
    {
        $this->setEntity($arguments->getEntity());

        if (!$this->isValidEntity()) {
            return;
        }

        $this->persist('setIdentifier', $this->entity->sanitize($this->entity->getTopic()));
    }

    public function postUpdate(LifecycleEventArgs $arguments)
    {
        $this->setEntity($arguments->getEntity());
        if (!$this->isValidEntity()) {
            return;
        }

        $this->persist('setIdentifier', $this->entity->sanitize($this->entity->getTopic()));
    }

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
