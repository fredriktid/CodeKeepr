<?php

namespace Frigg\KeeprBundle\Service;

use Symfony\Component\Yaml\Yaml;
use Frigg\KeeprBundle\Entity\Tag;

class PostService
{
    protected $em = null;
    protected $config= null;
    protected $securityContext = null;
    protected $currentUser = null;

    public function __construct($em, $securityContext, $configFile)
    {
        $this->em = $em;
        $this->config = $this->loadConfig($configFile);
        $this->securityContext = $securityContext;

        $token = $this->securityContext->getToken();
        if ($token) {
            $this->currentUser = $token->getUser();
        }
    }

    public function loadConfig($configFile)
    {
        return Yaml::parse(
            file_get_contents($configFile)
        );
    }

    public function getConfig($key)
    {
        return (isset($this->config[$key])) ? $this->config[$key] : null;
    }

    public function currentUser()
    {
        return $this->currentUser;
    }

    public function currentUserId()
    {
        return (is_object($this->currentUser)) ? $this->currentUser->getId() : 0;
    }

    public function loadById($id)
    {
        return $this->em
            ->getRepository('FriggKeeprBundle:Post')
            ->findOneById($id);
    }

    public function load()
    {
        $qb = $this->em->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'private' => 1,
                'current_user_id' => $this->currentUserId()
            ])
            ->getQuery()
            ->getResult();
    }

    public function loadTags()
    {
        $qb = $this->em->createQueryBuilder();
        return $qb->select('t')
            ->from('FriggKeeprBundle:Tag', 't')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function loadPopularTags($limit = null)
    {
        if ($limit === null) {
            $limit = $this->getConfig('tag_popular_limit');
        }

        $qb = $this->em->createQueryBuilder();
        return $qb->select('t.id, t.identifier, t.name, COUNT(p.id) AS post_count')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Tags', 't')
            ->where('t.id IS NOT NULL')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('post_count', 'DESC')
            ->groupBy('t.identifier')
            ->setMaxResults($limit)
            ->setParameters([
                'private' => 1,
                'current_user_id' => $this->currentUserId()
            ])
            ->getQuery()
            ->getResult();
    }

    public function loadByTag(Tag $tag)
    {
        $qb = $this->em->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Tags', 't')
            ->where('t.id = :tag_id')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'tag_id' => $tag->getId(),
                'private' => 1,
                'current_user_id' => $this->currentUserId()
            ])
            ->getQuery()
            ->getResult();
    }

    public function loadByUser($user = null)
    {
        if (null === $user) {
            $user = $this->currentUser;
        }

        $qb = $this->em->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->where($qb->expr()->eq(
                'p.User',
                ':user_id'
            ))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'user_id' => $user->getId(),
                'current_user_id' => $this->currentUserId(),
                'private' => 1,
            ])
            ->getQuery()
            ->getResult();
    }


    public function loadStarred($user = null)
    {
        if (null === $user) {
            $user = $this->currentUser;
        }

        $qb = $this->em->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Stars', 's')
            ->where(
                $qb->expr()->eq('s.User', ':user_id')
            )
            ->orderBy('s.created_at', 'DESC')
            ->setParameters([
                'user_id' => $user->getId()
            ])
            ->getQuery()
            ->getResult();
    }

    public function isStarred($post)
    {
        $star = $this->em->getRepository('FriggKeeprBundle:Star')->findOneBy([
            'User' => $this->currentUserId(),
            'Post' => $post->getId()
        ]);

        return ($star) ?: null;
    }

    public function loadPeriod($fromTs, $toTs)
    {
        $period = [
            'from_time' => new \DateTime(
                date('Y-m-d H:i:s', $fromTs)
            ),
            'to_time'   => new \DateTime(
                date('Y-m-d H:i:s', $toTs)
            )
        ];

        $qb = $this->em->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->where($qb->expr()->between(
                'p.created_at',
                ':from_time',
                ':to_time'
            ))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'from_time' => $period['from_time'],
                'to_time' => $period['to_time'],
                'private' => 1,
                'current_user_id' => $this->currentUserId()
            ])
            ->getQuery()
            ->getResult();
    }
}
