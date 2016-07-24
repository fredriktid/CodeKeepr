<?php

namespace Frigg\KeeprBundle\EventListener;

use Frigg\KeeprBundle\Sanitize\SanitizableIdentifierInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Sanitizes valid entities with a safe identifier (usually used as a slug)
 *
 * Class SanitizeIdentifierListener.
 */
class SanitizeIdentifierListener
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
