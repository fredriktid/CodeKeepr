<?php

namespace Frigg\KeeprBundle\Entity;

use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(name="github_id", type="string", length=255, nullable=true)
     */
    private $githubId;

    /**
     * @ORM\Column(name="github_token", type="string", length=255, nullable=true)
     */
    private $githubToken;

    /**
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     */
    private $googleId;

    /**
     * @ORM\Column(name="google_token", type="string", length=255, nullable=true)
     */
    private $googleToken;

    /**
     * @ORM\Column(name="reddit_id", type="string", length=255, nullable=true)
     */
    private $redditId;

    /**
     * @ORM\Column(name="reddit_token", type="string", length=255, nullable=true)
     */
    private $redditToken;

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
     * @param Post $post
     * @return User
     */
    public function addPost(Post $post)
    {
        $this->Posts[] = $post;

        return $this;
    }

    /**
     * Remove Posts.
     *
     * @param Post $post
     * @return User
     */
    public function removePost(Post $post)
    {
        $this->Posts->removeElement($post);

        return $this;
    }

    /**
     * Get Posts.
     *
     * @return Collection
     */
    public function getPosts()
    {
        return $this->Posts;
    }

    /**
     * Add Stars.
     *
     * @param Star $star
     * @return User
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
     * @return User
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
     * @param string $githubId
     * @return User
     */
    public function setGithubId($githubId)
    {
        $this->githubId = $githubId;

        return $this;
    }

    /**
     * @return string
     */
    public function getGithubId()
    {
        return $this->githubId;
    }

    /**
     * @param string $githubAccessToken
     * @return User
     */
    public function setGithubAccessToken($githubAccessToken)
    {
        $this->githubToken = $githubAccessToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGithubToken()
    {
        return $this->githubToken;
    }

    /**
     * @return string
     */
    public function getGithubAccessToken()
    {
        return $this->githubToken;
    }

    /**
     * @return mixed
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * @param $githubToken
     * @return User
     */
    public function setGithubToken($githubToken)
    {
        $this->githubToken = $githubToken;

        return $this;
    }

    /**
     * @param $googleId
     * @return User
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * @param $accessToken
     * @return User
     */
    public function setGoogleToken($accessToken)
    {
        $this->googleToken = $accessToken;

        return $this;
    }

    /**
     * @param $accessToken
     * @return User
     */
    public function setGoogleAccessToken($accessToken)
    {
        return $this->setGoogleToken($accessToken);
    }

    /**
     * @return mixed
     */
    public function getGoogleToken()
    {
        return $this->googleToken;
    }

    /**
     * @return mixed
     */
    public function getRedditId()
    {
        return $this->redditId;
    }

    /**
     * @param $redditId
     * @return User
     */
    public function setRedditId($redditId)
    {
        $this->redditId = $redditId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRedditToken()
    {
        return $this->redditToken;
    }

    /**
     * @param $redditToken
     * @return User
     */
    public function setRedditToken($redditToken)
    {
        $this->redditToken = $redditToken;

        return $this;
    }

    /**
     * @param $redditToken
     * @return User
     */
    public function setRedditAccessToken($redditToken)
    {
        return $this->setRedditToken($redditToken);
    }
}
