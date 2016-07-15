<?php

namespace Frigg\KeeprBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Frigg\KeeprBundle\Entity\Repository\StarRepository")
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
     * @ORM\Column(type="datetime")
     */
    private $created_at;

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
     * @param User $user
     * @return Star
     */
    public function setUser(User $user)
    {
        $this->User = $user;

        return $this;
    }

    /**
     * Get User
     *
     * @return User
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * Set Post
     *
     * @param Post $post
     * @return Star
     */
    public function setPost(Post $post)
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

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Star
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @ORM\PrePersist
     */
    public function updateTimestamps()
    {
        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        }
    }
}
