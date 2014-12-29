<?php

namespace Frigg\KeeprBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Frigg\KeeprBundle\Entity\Post;

class CommentRepository extends EntityRepository
{
    public function postCount(Post $post)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('COUNT(c)')
            ->from('FriggKeeprBundle:Comment', 'c')
            ->where('c.thread = :thread')
            ->setParameters([
                'thread' => sprintf('post_%d', $post->getId())
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
