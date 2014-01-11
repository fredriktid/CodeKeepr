<?php
namespace Frigg\KeeprBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 */
class Star
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Frigg\KeeprBundle\Entity\User", inversedBy="Stars")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $User;

    /**
     * @ORM\ManyToOne(targetEntity="Frigg\KeeprBundle\Entity\Post", inversedBy="Stars")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", nullable=false)
     */
    private $Post;

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
     * Set User
     *
     * @param \Frigg\KeeprBundle\Entity\User $user
     * @return Star
     */
    public function setUser(\Frigg\KeeprBundle\Entity\User $user)
    {
        $this->User = $user;

        return $this;
    }

    /**
     * Get User
     *
     * @return \Frigg\KeeprBundle\Entity\User
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * Set Post
     *
     * @param \Frigg\KeeprBundle\Entity\Post $post
     * @return Star
     */
    public function setPost(\Frigg\KeeprBundle\Entity\Post $post)
    {
        $this->Post = $post;

        return $this;
    }

    /**
     * Get Post
     *
     * @return \Frigg\KeeprBundle\Entity\Post
     */
    public function getPost()
    {
        return $this->Post;
    }
}
