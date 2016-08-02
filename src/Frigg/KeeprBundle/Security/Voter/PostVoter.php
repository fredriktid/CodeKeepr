<?php

namespace Frigg\KeeprBundle\Security\Voter;

use Frigg\KeeprBundle\Entity\Post;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class PostVoter.
 */
class PostVoter extends BaseVoter implements VoterInterface
{
    /**
     * @param string $attribute
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        return 0 === strpos($attribute, 'POST');
    }

    /**
     * @param TokenInterface $token
     * @param null|object    $postEntity
     * @param array          $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $postEntity, array $attributes)
    {
        if (!($postEntity instanceof Post)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $this->loadRolesFromToken($token);

        if (in_array('ROLE_ADMIN', $this->currentUserRoles)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            switch ($attribute) {
                case 'POST_STAR':
                case 'POST_STAR_NEW':
                case 'POST_STAR_REMOVE':
                case 'POST_NEW':
                    if ($this->isLoggedIn()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
                case 'POST_DELETE':
                case 'POST_EDIT':
                    if ($this->isOwnedByUserId($postEntity->getUser()->getId())) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
                case 'POST_SHOW':
                    if (!$postEntity->getPrivate()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }

                    if ($this->isOwnedByUserId($postEntity->getUser()->getId())) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
