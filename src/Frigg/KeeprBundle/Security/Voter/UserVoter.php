<?php

namespace Frigg\KeeprBundle\Security\Voter;

use Frigg\KeeprBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class UserVoter.
 */
class UserVoter extends BaseVoter implements VoterInterface
{
    /**
     * @param string $attribute
     * @return bool
     */
    public function supportsAttribute($attribute)
    {
        return 0 === strpos($attribute, 'USER');
    }

    /**
     * @param TokenInterface $token
     * @param null|object    $userEntity
     * @param array          $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $userEntity, array $attributes)
    {
        if (!$this->supportsClass($userEntity)) {
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
                case 'USER_STAR_DELETE':
                case 'USER_STAR_SHOW':
                case 'USER_PRIVATE_POSTS':
                case 'USER_POSTS':
                    if ($this->isOwnedByUserId($userEntity->getId())) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
                case 'USER_STAR_NEW':
                    if ($this->isLoggedIn()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
