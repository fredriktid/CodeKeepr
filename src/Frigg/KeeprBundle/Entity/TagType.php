<?php

namespace Frigg\KeeprBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class TagType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Frigg\KeeprBundle\Entity\Tag", mappedBy="TagType")
     */
    private $Tags;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->Tags = new ArrayCollection();
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
     * Set name.
     *
     * @param string $name
     * @return TagType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add Tags.
     *
     * @param Tag $tag
     * @return TagType
     */
    public function addTag(Tag $tag)
    {
        $this->Tags[] = $tag;

        return $this;
    }

    /**
     * Remove Tags.
     *
     * @param Tag $tag
     * @return TagType
     */
    public function removeTag(Tag $tag)
    {
        $this->Tags->removeElement($tag);

        return $this;
    }

    /**
     * Get Tags.
     *
     * @return Collection
     */
    public function getTags()
    {
        return $this->Tags;
    }
}
