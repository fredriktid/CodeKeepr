<?php

namespace Frigg\KeeprBundle\EventListener;

use Frigg\KeeprBundle\Sanitize\SanitizableIdentifierInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class ActivityListener.
 */
class ActivityListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var SanitizableIdentifierInterface
     */
    protected $entity;

    /**
     * ActivityListener constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param SanitizableIdentifierInterface $entity
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
        return ($this->entity instanceof SanitizableIdentifierInterface);
    }

    /**
     * @param LifecycleEventArgs $arguments
     */
    public function postPersist(LifecycleEventArgs $arguments)
    {
        /** @var SanitizableIdentifierInterface $entity */
        $entity = $arguments->getEntity();
        $this->setEntity($entity);

        if (!$this->isValidEntity()) {
            return;
        }

        $this->persist('setIdentifier', $this->entity->generateSanitizedIdentifier());
    }

    /**
     * @param LifecycleEventArgs $arguments
     */
    public function postUpdate(LifecycleEventArgs $arguments)
    {
        /** @var SanitizableIdentifierInterface $entity */
        $entity = $arguments->getEntity();
        $this->setEntity($entity);

        if (!$this->isValidEntity()) {
            return;
        }

        $this->persist('setIdentifier', $this->entity->generateSanitizedIdentifier());
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
