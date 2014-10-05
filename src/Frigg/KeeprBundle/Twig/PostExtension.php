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

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('is_starred', [$this, 'isStarred'])
        ];
    }

    public function isStarred($post)
    {
       return $this->postService->isStarred($post);
    }
}
