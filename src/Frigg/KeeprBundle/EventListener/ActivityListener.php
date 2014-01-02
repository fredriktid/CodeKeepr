<?php

namespace Frigg\KeeprBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

class ActivityListener
{
    protected $container;
    protected $validEntities = array(
        'Post'
    );

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function isValidEntity($entity)
    {
        $classChunks = explode('\\', get_class($entity));
        return in_array(end($classChunks), $this->validEntities);
    }

    public function postPersist(LifecycleEventArgs $arguments)
    {
        $entity = $arguments->getEntity();
        if (!$this->isValidEntity($entity)) {
            return;
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $identifier = sprintf('%s-%d', $entity->sanitize($entity->getTopic()), $entity->getId());
        $entity->setIdentifier($identifier);
        $em->persist($entity);
        $em->flush();
    }
}
