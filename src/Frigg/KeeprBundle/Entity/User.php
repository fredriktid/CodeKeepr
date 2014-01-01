<?php

namespace Frigg\KeeprBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="FOSUser")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Frigg\KeeprBundle\Entity\Post", mappedBy="User")
     */
    private $Posts;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add Posts
     *
     * @param \Frigg\KeeprBundle\Entity\Post $posts
     * @return User
     */
    public function addPost(\Frigg\KeeprBundle\Entity\Post $posts)
    {
        $this->Posts[] = $posts;

        return $this;
    }

    /**
     * Remove Posts
     *
     * @param \Frigg\KeeprBundle\Entity\Post $posts
     */
    public function removePost(\Frigg\KeeprBundle\Entity\Post $posts)
    {
        $this->Posts->removeElement($posts);
    }

    /**
     * Get Posts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPosts()
    {
        return $this->Posts;
    }
}
