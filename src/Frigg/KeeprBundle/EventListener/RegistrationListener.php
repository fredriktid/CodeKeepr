<?php

namespace Frigg\KeeprBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class RegistrationListener.
 */
class RegistrationListener implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * RegistrationListener constructor.
     *
     * @param UrlGeneratorInterface $router
     */
    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onRegistrationSuccess(FormEvent $event)
    {
        $url = $this->router->generate('home');
        $event->setResponse(new RedirectResponse($url));
    }
}
