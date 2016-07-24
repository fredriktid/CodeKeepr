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
     * @return Star|null
     */
    public function isStarred(Post $post, User $user)
    {
        /** @var Star $star */
        $star = $this->findOneBy([
            'Post' => $post->getId(),
            'User' => $user->getId(),
        ]);

        return ($star) ?: null;
    }
}
