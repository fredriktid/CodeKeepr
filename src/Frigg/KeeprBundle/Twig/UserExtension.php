<?php

namespace Frigg\KeeprBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class UserExtension extends \Twig_Extension
{
    protected $container;
    protected $userService;
    protected $postService;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->userService = $container->get('codekeepr.service.user');
        $this->postService = $container->get('codekeepr.service.post');
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('username', array($this, 'generateUsername')),
            new \Twig_SimpleFilter('is_starred', array($this, 'isStarred')),
            new \Twig_SimpleFilter('stars', array($this, 'getStars'))
        );
    }

    public function factory($user, $method, $params = array())
    {
        call_user_method_array(array($this->userService, $method), $params);
    }

    public function isStarred($postEntity, $starGroup)
    {
        $this->postService->setEntity($postEntity);
        return !$this->postService->canStarEntity($starGroup);
    }

    public function getStars($userId)
    {
        $this->userService->loadEntityById($userId);
        $this->postService->setUserService($this->userService);
        $this->postService->loadStarredByUser();
        return $this->postService->getCollectionIds();
    }

    public function generateUsername($user)
    {
        $this->userService->setEntity($user);
        return $this->userService->generateUsername();
    }

    public function getName()
    {
        return 'user_extension';
    }
}
