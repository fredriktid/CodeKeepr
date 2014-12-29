<?php

namespace Frigg\KeeprBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Frigg\KeeprBundle\Entity\Tag;

class PostRepository extends EntityRepository
{
    public function loadPublic()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->where('p.private != :private')
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'private' => 1
            ])
            ->getQuery()
            ->getResult();
    }

    public function loadPrivate($user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->where('p.User = :user_id')
            ->andWhere('p.private = :private')
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'user_id' => $user->getId(),
                'private' => 1
            ])
            ->getQuery()
            ->getResult();
    }


    public function loadByTag(Tag $tag)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Tags', 't')
            ->where('t.id = :tag_id')
            ->andWhere('p.private != :private')
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'tag_id' => $tag->getId(),
                'private' => 1
            ])
            ->getQuery()
            ->getResult();
    }

    public function loadByUser($user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->where('p.User = :user_id')
            ->andWhere('p.private != :private')
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'user_id' => $user->getId(),
                'private' => 1
            ])
            ->getQuery()
            ->getResult();
    }


    public function loadStarred($user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Stars', 's')
            ->where('s.User = :user_id')
            ->orderBy('s.created_at', 'DESC')
            ->setParameters([
                'user_id' => $user->getId()
            ])
            ->getQuery()
            ->getResult();
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

        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->where($qb->expr()->between(
                'p.created_at',
                ':from_time',
                ':to_time'
            ))
            ->andWhere('p.private != :private')
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'from_time' => $period['from_time'],
                'to_time' => $period['to_time'],
                'private' => 1,
            ])
            ->getQuery()
            ->getResult();
    }
}
