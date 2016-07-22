<?php

namespace Frigg\KeeprBundle\EventListener;

use Frigg\KeeprBundle\Sanitize\SanitizableIdentifierInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Updates valid entities with a safe, sanitized identifier (usually used as a slug)
 *
 * Class UpdateIdentifierListener.
 */
class   UpdateIdentifierListener
{
    /**
     * UpdateIdentifierListener constructor.
     *
     */
    public function __construct()
    {
    }

    /**
     * @param LifecycleEventArgs $arguments
     */
    public function prePersist(LifecycleEventArgs $arguments)
    {
        $entity = $arguments->getEntity();
        $this->setIdentifier($entity);
    }

    /**
     * @param LifecycleEventArgs $arguments
     */
    public function preUpdate(LifecycleEventArgs $arguments)
    {
        $entity = $arguments->getEntity();
        $this->setIdentifier($entity);
    }

    /**
     * @param mixed $entity
     * @return null|SanitizableIdentifierInterface
     */
    public function setIdentifier($entity)
    {
        if (!($entity instanceof SanitizableIdentifierInterface)) {
            return null;
        }

        $entity->setIdentifier($entity->generateIdentifier());

        return $entity;
    }
}
