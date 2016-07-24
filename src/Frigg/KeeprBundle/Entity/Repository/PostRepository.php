<?php

namespace Frigg\KeeprBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserInterface;
use Frigg\KeeprBundle\Entity\Tag;
use Frigg\KeeprBundle\Entity\User;

/**
 * Class PostRepository.
 */
class PostRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findPublicPosts()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->where('p.private != :private')
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'private' => 1,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    public function findPrivateByUser(UserInterface $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->where('p.User = :user_id')
            ->andWhere('p.private = :private')
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'user_id' => $user->getId(),
                'private' => 1,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Tag $tag
     * @return array
     */
    public function findPublicByTag(Tag $tag)
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
                'private' => 1,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    public function findPublicByUser(UserInterface $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->where('p.User = :user_id')
            ->andWhere('p.private != :private')
            ->orderBy('p.created_at', 'DESC')
            ->setParameters([
                'user_id' => $user->getId(),
                'private' => 1,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    public function findStarredByUser(UserInterface $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Stars', 's')
            ->where('s.User = :user_id')
            ->orderBy('s.created_at', 'DESC')
            ->setParameters([
                'user_id' => $user->getId(),
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $fromTs
     * @param $toTs
     * @return array
     */
    public function findInTimePeriod($fromTs, $toTs)
    {
        $period = [
            'from_time' => new \DateTime(
                date('Y-m-d H:i:s', $fromTs)
            ),
            'to_time' => new \DateTime(
                date('Y-m-d H:i:s', $toTs)
            ),
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
