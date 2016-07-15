<?php

namespace Frigg\KeeprBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Language
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", unique=true, length=255)
     */
    private $identifier;

    /**
     * @ORM\OneToMany(targetEntity="Frigg\KeeprBundle\Entity\Post", mappedBy="Language")
     */
    private $Posts;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Posts = new ArrayCollection();
    }

    /**
     * String representation of entity
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
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
     * Set name
     *
     * @param string $name
     * @return Language
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setIdentifier($name);

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

   /**
     * Sanitize a string to create an identifier
     *
     * @param string $string
     * @return string
     */
    public function sanitize($string)
    {
        return trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($string)), '_');
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return Language
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $this->sanitize($identifier);

        return $this;
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Add Posts
     *
     * @param Post $posts
     * @return Language
     */
    public function addPost(Post $posts)
    {
        $this->Posts[] = $posts;

        return $this;
    }

    /**
     * Remove Posts
     *
     * @param Post $posts
     * @return Language
     */
    public function removePost(Post $posts)
    {
        $this->Posts->removeElement($posts);

        return $this;
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
