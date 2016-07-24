<?php

namespace Frigg\KeeprBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Frigg\KeeprBundle\Sanitize\SanitizableIdentifierInterface;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Frigg\KeeprBundle\Entity\Repository\PostRepository")
 */
class Post implements SanitizableIdentifierInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modified_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $topic;

    /**
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     */
    private $identifier;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $private;

    /**
     * @ORM\ManyToOne(targetEntity="Frigg\KeeprBundle\Entity\User", inversedBy="Posts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $User;

    /**
     * @ORM\ManyToMany(targetEntity="Frigg\KeeprBundle\Entity\Tag", mappedBy="Posts")
     */
    private $Tags;

    /**
     * @ORM\ManyToOne(targetEntity="Frigg\KeeprBundle\Entity\Language", inversedBy="Posts")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id", nullable=false)
     */
    private $Language;

    /**
     * @ORM\OneToMany(targetEntity="Frigg\KeeprBundle\Entity\Star", mappedBy="Post")
     */
    private $Stars;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enable_comments;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hidden;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->Tags = new ArrayCollection();
        $this->Stars = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->topic;
    }

    /**
     * Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return a sanitized identifier
     *
     * @return string
     */
    public function generateIdentifier()
    {
        return trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($this->getTopic())), '_');
    }

    /**
     * Set identifier.
     *
     * @param string $identifier
     *
     * @return Post
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier.
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
        $this->setModifiedAt(new \DateTime(date('Y-m-d H:i:s')));

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        }
    }

    /**
     * Set created_at.
     *
     * @param \DateTime $createdAt
     *
     * @return Post
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set modified_at.
     *
     * @param \DateTime $modifiedAt
     *
     * @return Post
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modified_at = $modifiedAt;

        return $this;
    }

    /**
     * Get modified_at.
     *
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modified_at;
    }

    /**
     * Set topic.
     *
     * @param string $topic
     *
     * @return Post
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic.
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Post
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set User.
     *
     * @param User $user
     *
     * @return Post
     */
    public function setUser(User $user)
    {
        $this->User = $user;

        return $this;
    }

    /**
     * Get User.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * Add Tags.
     *
     * @param Tag $tag
     *
     * @return Post
     */
    public function addTag(Tag $tag)
    {
        if (!$this->Tags->contains($tag)) {
            $this->Tags->add($tag);
        }

        return $this;
    }

    /**
     * Remove Tags.
     *
     * @param Tag $tag
     * @return Post
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

    /**
     * Set Language.
     *
     * @param Language $language
     * @return Post
     */
    public function setLanguage(Language $language)
    {
        $this->Language = $language;

        return $this;
    }

    /**
     * Get Language.
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->Language;
    }

    /**
     * Add Stars.
     *
     * @param Star $star
     * @return Post
     */
    public function addStar(Star $star)
    {
        $this->Stars[] = $star;

        return $this;
    }

    /**
     * Remove Stars.
     *
     * @param Star $star
     */
    public function removeStar(Star $star)
    {
        $this->Stars->removeElement($star);

        return $this;
    }

    /**
     * Get Stars.
     *
     * @return Collection
     */
    public function getStars()
    {
        return $this->Stars;
    }

    /**
     * Set private.
     *
     * @param bool $private
     * @return Post
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get private.
     *
     * @return bool
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * Is this a public post?
     *
     * @return bool
     */
    public function isPublic()
    {
        return (!$this->getPrivate() && !$this->getHidden());
    }

    /**
     * @return book
     */
    public function getEnableComments()
    {
        return $this->enable_comments;
    }

    /**
     * @param bool $enableComments
     */
    public function setEnableComments($enableComments)
    {
        $this->enable_comments = $enableComments;
    }

    /**
     * @return bool
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     * @return Post
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }
}
