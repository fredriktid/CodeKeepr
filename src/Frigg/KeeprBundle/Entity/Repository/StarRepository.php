<?php

namespace Frigg\KeeprBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class StarRepository extends EntityRepository
{
    public function isStarred($post, $user)
    {
        $em = $this->getEntityManager();
        $star = $em->getRepository('FriggKeeprBundle:Star')->findOneBy([
            'Post' => $post->getId(),
            'User' => $user->getId()
        ]);

        return ($star) ?: null;
    }
}
