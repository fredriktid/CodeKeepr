<?php

namespace Frigg\KeeprBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Thread as BaseThread;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Thread extends BaseThread
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;
}
