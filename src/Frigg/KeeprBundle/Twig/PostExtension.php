<?php

namespace Frigg\KeeprBundle\Twig;

use Frigg\KeeprBundle\Entity\Post;
use Frigg\KeeprBundle\Entity\User;

class PostExtension extends \Twig_Extension
{
    protected $postService;

    public function __construct($postService)
    {
        $this->postService = $postService;
    }

    public function getName()
    {
        return 'post_extension';
    }

    public function getGlobals()
    {
        return array(
            'current_user_stars' => array()
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('is_starred', array($this, 'isStarred')),
            new \Twig_SimpleFilter('stars', array($this, 'stars'))
        );
    }

    public function isStarred(Post $postEntity)
    {
       return false;
    }

    public function stars(User $userEntity)
    {
        return array();
    }
}
