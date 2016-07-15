<?php

namespace Frigg\KeeprBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class TagRepository
 * @package Frigg\KeeprBundle\Entity\Repository
 */
class TagRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function loadPublic()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('t')
            ->from('FriggKeeprBundle:Tag', 't')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $limit
     * @return array
     */
    public function loadPopular($limit = 15)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('t.id, t.identifier, t.name, COUNT(p.id) AS post_count')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Tags', 't')
            ->where('t.id IS NOT NULL')
            ->andWhere('p.private != :private')
            ->orderBy('post_count', 'DESC')
            ->groupBy('t.identifier')
            ->setMaxResults($limit)
            ->setParameters([
                'private' => 1
            ])
            ->getQuery()
            ->getResult();
    }
}
