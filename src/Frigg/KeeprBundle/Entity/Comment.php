<?php

namespace Frigg\KeeprBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Comment as BaseComment;
use FOS\CommentBundle\Model\RawCommentInterface;
use FOS\CommentBundle\Model\SignedCommentInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\CommentBundle\Model\VotableCommentInterface;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\Entity(repositoryClass="Frigg\KeeprBundle\Entity\Repository\CommentRepository")
 */
class Comment extends BaseComment implements SignedCommentInterface, VotableCommentInterface, RawCommentInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Thread of this comment.
     *
     * @var Thread
     * @ORM\ManyToOne(targetEntity="Frigg\KeeprBundle\Entity\Thread")
     */
    protected $thread;

    /**
     * Author of the comment.
     *
     * @ORM\ManyToOne(targetEntity="Frigg\KeeprBundle\Entity\User")
     *
     * @var User
     */
    protected $author;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $score = 0;

    /**
     * @ORM\Column(name="rawBody", type="text", nullable=true)
     *
     * @var string
     */
    protected $rawBody;

    /**
     * @param string $rawBody
     */
    public function setRawBody($rawBody)
    {
        $this->rawBody = $rawBody;
    }

    /**
     * @return string
     */
    public function getRawBody()
    {
        return $this->rawBody;
    }

    /**
     * @param UserInterface $author
     */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        if (null === $this->getAuthor()) {
            return 'Anonymous';
        }

        return $this->getAuthor()->getUsername();
    }

    /**
     * Sets the score of the comment.
     *
     * @param int $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Returns the current score of the comment.
     *
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Increments the comment score by the provided
     * value.
     *
     * @param int $by
     *
     * @return int The new comment score
     */
    public function incrementScore($by = 1)
    {
        $this->score += $by;
    }
}
