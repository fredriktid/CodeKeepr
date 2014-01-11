<?php

namespace Frigg\KeeprBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Tag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=false, length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", unique=false, length=255)
     */
    private $identifier;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity="Frigg\KeeprBundle\Entity\TagType", inversedBy="Tags")
     * @ORM\JoinColumn(name="tag_type_id", referencedColumnName="id", nullable=true)
     */
    private $TagType;

    /**
     * @ORM\ManyToMany(targetEntity="Frigg\KeeprBundle\Entity\Post", inversedBy="Tags")
     * @ORM\JoinTable(
     *     name="PostToTag",
     *     joinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id", nullable=false)}
     * )
     */
    private $Posts;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Posts = new ArrayCollection();
    }

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
     * @return Tag
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
     * @return Tag
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
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps()
    {
        // update created time
        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        }
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Flight
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
     * Set TagType
     *
     * @param \Frigg\KeeprBundle\Entity\TagType $tagType
     * @return Tag
     */
    public function setTagType(\Frigg\KeeprBundle\Entity\TagType $tagType)
    {
        $this->TagType = $tagType;

        return $this;
    }

    /**
     * Get TagType
     *
     * @return \Frigg\KeeprBundle\Entity\TagType
     */
    public function getTagType()
    {
        return $this->TagType;
    }

    /**
     * Add Posts
     *
     * @param \Frigg\KeeprBundle\Entity\Post $posts
     * @return Tag
     */
    public function addPost(\Frigg\KeeprBundle\Entity\Post $posts)
    {
        if (!$this->Posts->contains($posts)) {
            $this->Posts->add($posts);
        }

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
