<?php

namespace Frigg\KeeprBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Frigg\KeeprBundle\Entity\Post;
use Frigg\KeeprBundle\Entity\Star;
use Frigg\KeeprBundle\Entity\User;

/**
 * Class StarRepository.
 */
class StarRepository extends EntityRepository
{
    /**
     * @param Post $post
     * @param User $user
     * @return true|null
     */
    public function isStarred(Post $post, User $user)
    {
        $em = $this->getEntityManager();

        /** @var Star $star */
        $star = $em->getRepository('FriggKeeprBundle:Star')->findOneBy([
            'Post' => $post->getId(),
            'User' => $user->getId(),
        ]);

        return ($star) ?: null;
    }
}
