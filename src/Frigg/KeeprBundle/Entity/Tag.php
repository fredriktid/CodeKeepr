<?php
namespace Frigg\KeeprBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
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
     * @ORM\Column(nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(nullable=true)
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Frigg\KeeprBundle\Entity\TagType", inversedBy="Tags")
     * @ORM\JoinColumn(name="tag_type_id", referencedColumnName="id", nullable=false)
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
        $this->Posts = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set date
     *
     * @param string $date
     * @return Tag
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
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