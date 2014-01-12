<?php

namespace Frigg\KeeprBundle\Service;

use Doctrine\ORM\EntityManager;
use Frigg\KeeprBundle\Entity\User;
use Frigg\KeeprBundle\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\ElasticaBundle\Finder\TransformedFinder;

class PostService extends ParentService
{
    protected $finder;
    protected $userService = null;

    public function __construct(EntityManager $em, TransformedFinder $finder, $configFile)
    {
        parent::__construct($em, $configFile);
        $this->finder = $finder;
    }

    public function loadEntityById($id)
    {
        $this->entity = $this->em->getRepository('FriggKeeprBundle:Post')->findOneById($id);
        return $this;
    }

    public function loadEntityByIdentifier($identifier)
    {
        $this->entity = $this->em->getRepository('FriggKeeprBundle:Post')->findOneByIdentifier($identifier);
        return $this;
    }

    public function getFinder()
    {
        return $this->finder;
    }

    public function getUserService()
    {
        return $this->userService;
    }

    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    public function loadAll()
    {
        if (!is_object($this->getUserService())) {
            $this->collection = array();
            return $this->collection();
        }

        $qb = $this->em->createQueryBuilder();
        $this->collection = $qb->select('p')
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
                'current_user_id' => $this->getUserService()->getCurrentUserId()
            ))
            ->getQuery()->getResult();

        return $this;
    }

    public function loadByUser()
    {
        if (!is_object($this->getUserService())) {
            $this->collection = array();
            return $this->collection();
        }

        $qb = $this->em->createQueryBuilder();
        $this->collection = $qb->select('p')
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
                'user_id' => $this->getUserService()->getEntityId(),
                'current_user_id' => $this->getUserService()->getCurrentUserId(),
                'private' => 1,
            ))
            ->getQuery()->getResult();

        return $this;
    }

    public function canStarEntity($currentStars = null)
    {
        if (!is_object($this->entity)) {
            return false;
        }

        if ($currentStars === null) {
            $this->loadStarredByUser();
            $currentStars = $this->getCollectionIds();
        }

        return (bool)!(static::arraySearchRecursive(
            $this->entity->getId(),
            $currentStars
        ));
    }

    public function loadStarredByUser()
    {
        if (!is_object($this->getUserService())) {
            $this->collection = array();
            return $this->collection();
        }

        $qb = $this->em->createQueryBuilder();
        $this->collection = $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Stars', 's')
            ->where(
                $qb->expr()->eq('s.User', ':user_id')
            )
            ->orderBy('s.created_at', 'DESC')
            ->setParameters(array(
                'user_id' => $this->getUserService()->getEntityId()
            ))
            ->getQuery()->getResult();

        return $this;
    }

    public function loadByDay($timestamp)
    {
        if (!is_object($this->getUserService())) {
            $this->collection = array();
            return $this->collection();
        }

        $timeInterval = array(
            'from_time' => mktime(0, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp)),
            'to_time'   => mktime(23, 59, 59, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp))
        );
        $dateInterval = array(
            'from_time' => new \DateTime(date('Y-m-d H:i:s', $timeInterval['from_time'])),
            'to_time'   => new \DateTime(date('Y-m-d H:i:s', $timeInterval['to_time']))
        );

        $qb = $this->em->createQueryBuilder();
        $this->collection = $qb->select('p')
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
                'from_time' => $dateInterval['from_time'],
                'to_time' => $dateInterval['to_time'],
                'private' => 1,
                'current_user_id' => $this->getUserService()->getCurrentUserId()
            ))
            ->getQuery()->getResult();

        return $this;
    }
}
