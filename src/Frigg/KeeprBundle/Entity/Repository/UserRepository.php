<?php

namespace Frigg\KeeprBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class UserRepository.
 */
class UserRepository extends EntityRepository
{
    /**
     * @param $limit
     * @return array
     */
    public function findActiveByPostCount($limit)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('u.id, u.email, COUNT(p.id) AS post_count')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.User', 'u')
            ->where('u.id IS NOT NULL')
            ->orderBy('post_count', 'DESC')
            ->groupBy('u.id')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
