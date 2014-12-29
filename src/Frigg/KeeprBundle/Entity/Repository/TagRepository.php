<?php

namespace Frigg\KeeprBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    public function loadPublic()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('t')
            ->from('FriggKeeprBundle:Tag', 't')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function loadPopular($limit = 20)
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
