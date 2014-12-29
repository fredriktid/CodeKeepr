<?php

namespace Frigg\KeeprBundle\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends BaseVoter implements VoterInterface
{
    public function supportsAttribute($attribute)
    {
        return (0 === strpos($attribute, 'USER'));
    }

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
                case 'USER_POSTS':
                    if (is_object($this->currentUser)) {
                        if ($userEntity->getId() == $this->currentUser->getId()) {
                            return VoterInterface::ACCESS_GRANTED;
                        }
                    }
                    break;
                case 'USER_STAR_NEW':
                    if (is_object($this->currentUser)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
            }

            return VoterInterface::ACCESS_DENIED;
        }
    }
}
