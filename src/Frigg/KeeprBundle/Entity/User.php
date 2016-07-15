<?php

namespace Frigg\KeeprBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="FOSUser")
 * @ORM\Entity(repositoryClass="Frigg\KeeprBundle\Entity\Repository\UserRepository")
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

    /**
     * @ORM\OneToMany(targetEntity="Frigg\KeeprBundle\Entity\Star", mappedBy="User")
     */
    private $Stars;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->Posts = new ArrayCollection();
        $this->Stars = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->generateUsername();
    }

    /**
     * Generate username based on email address.
     *
     * @param null $email
     *
     * @return string
     */
    public function generateUsername($email = null)
    {
        if (null === $email) {
            $email = $this->getEmail();
        }

        $username = '';
        foreach (str_split($email) as $char) {
            if (!preg_match('/^[A-Za-z\\-]$/', $char)) {
                break;
            }
            $username .= $char;
        }

        return $username;
    }

    /**
     * Override to exclude username.
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        return $this;
    }

    /**
     * Override to set email and username.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->username = $email;

        return $this;
    }

    /**
     * Override to set canonical email and username.
     *
     * @param string $emailCanonical
     *
     * @return User
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;
        $this->usernameCanonical = $emailCanonical;

        return $this;
    }

    /**
     * Add Posts.
     *
     * @param \Frigg\KeeprBundle\Entity\Post $posts
     *
     * @return User
     */
    public function addPost(\Frigg\KeeprBundle\Entity\Post $posts)
    {
        $this->Posts[] = $posts;

        return $this;
    }

    /**
     * Remove Posts.
     *
     * @param \Frigg\KeeprBundle\Entity\Post $posts
     */
    public function removePost(\Frigg\KeeprBundle\Entity\Post $posts)
    {
        $this->Posts->removeElement($posts);
    }

    /**
     * Get Posts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPosts()
    {
        return $this->Posts;
    }

    /**
     * Add Stars.
     *
     * @param \Frigg\KeeprBundle\Entity\Star $stars
     *
     * @return User
     */
    public function addStar(\Frigg\KeeprBundle\Entity\Star $stars)
    {
        $this->Stars[] = $stars;

        return $this;
    }

    /**
     * Remove Stars.
     *
     * @param \Frigg\KeeprBundle\Entity\Star $stars
     */
    public function removeStar(\Frigg\KeeprBundle\Entity\Star $stars)
    {
        $this->Stars->removeElement($stars);
    }

    /**
     * Get Stars.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStars()
    {
        return $this->Stars;
    }
}
