<?php

namespace Frigg\KeeprBundle\Twig;

use Frigg\KeeprBundle\Service\UserServiceInterface;
use Frigg\KeeprBundle\Service\UserContainerInterface;

class UserExtension extends \Twig_Extension
{
    protected $container;
    protected $userService;
    protected $postService;

    public function __construct(UserServiceInterface $userService, UserContainerInterface $postService)
    {
        $this->userService = $userService;
        $this->postService = $postService;
    }

    public function getGlobals()
    {
        return array(
            'user_stars' => $this->userService->getStars()
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('username', array($this, 'generateUsername')),
            new \Twig_SimpleFilter('is_starred', array($this, 'isStarred')),
            new \Twig_SimpleFilter('stars', array($this, 'getStars'))
        );
    }

    public function isStarred($postEntity, $currentStars)
    {
        $this->postService->setEntity($postEntity);
        return !$this->postService->canStarEntity($currentStars);
    }

    public function getStars($userId)
    {
        $this->userService->loadEntityById($userId);
        $this->postService->setUserService($this->userService);
        $this->postService->loadUserStarPosts();
        return $this->postService->getLoadedCollectionIds();
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
