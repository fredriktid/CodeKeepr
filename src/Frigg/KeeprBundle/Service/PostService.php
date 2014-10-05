<?php

namespace Frigg\KeeprBundle\Service;

use Doctrine\ORM\EntityManager;
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
            ->setParameters(array(
                'private' => 1,
                'current_user_id' => $this->currentUserId()
            ))
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
            $limit = $this->getConfig('tag_group_limit');
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
            ->setParameters(array(
                'private' => 1,
                'current_user_id' => $this->currentUserId()
            ))
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
            ->setParameters(array(
                'tag_id' => $tag->getId(),
                'private' => 1,
                'current_user_id' => $this->currentUserId()
            ))
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
            ->setParameters(array(
                'user_id' => $user->getId(),
                'current_user_id' => $this->currentUserId(),
                'private' => 1,
            ))
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
            ->setParameters(array(
                'user_id' => $user->getId()
            ))
            ->getQuery()
            ->getResult();
    }

    public function loadDay($timestamp)
    {
        $dayTime = array(
            'from_time' => new \DateTime(
                date('Y-m-d H:i:s',
                    mktime(0, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp))
                )
            ),
            'to_time'   => new \DateTime(
                date('Y-m-d H:i:s',
                    mktime(23, 59, 59, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp))
                )
            )
        );

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
            ->setParameters(array(
                'from_time' => $dayTime['from_time'],
                'to_time' => $dayTime['to_time'],
                'private' => 1,
                'current_user_id' => $this->currentUserId()
            ))
            ->getQuery()
            ->getResult();
    }
}
