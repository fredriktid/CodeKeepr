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
class UpdateIdentifierListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * UpdateIdentifierListener constructor.
     *
     * So the whole containers needs to be injected to prevent a circular reference with the entity manager
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param LifecycleEventArgs $arguments
     */
    public function postPersist(LifecycleEventArgs $arguments)
    {
        $entity = $arguments->getEntity();
        $this->setIdentifier($entity);
    }

    /**
     * @param LifecycleEventArgs $arguments
     */
    public function postUpdate(LifecycleEventArgs $arguments)
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

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($entity);
        $em->flush();

        return $entity;
    }
}
