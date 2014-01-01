<?php
namespace Frigg\KeeprBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

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
     * Constructor
     */
    public function __construct()
    {
        $this->Tags = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return TagType
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * Add Tags
     *
     * @param \Frigg\KeeprBundle\Entity\Tag $tags
     * @return TagType
     */
    public function addTag(\Frigg\KeeprBundle\Entity\Tag $tags)
    {
        $this->Tags[] = $tags;

        return $this;
    }

    /**
     * Remove Tags
     *
     * @param \Frigg\KeeprBundle\Entity\Tag $tags
     */
    public function removeTag(\Frigg\KeeprBundle\Entity\Tag $tags)
    {
        $this->Tags->removeElement($tags);
    }

    /**
     * Get Tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->Tags;
    }
}
