<?php

namespace Frigg\KeeprBundle\Security\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Doctrine\ORM\EntityManager;

class UserVoter extends BaseVoter implements VoterInterface
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, 'USER_');
    }

    public function vote(TokenInterface $token, $userEntity, array $attributes)
    {
        if (!$this->supportsClass($userEntity)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $this->setCurrentUser($token);

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            switch ($this->securedArea($attribute)) {
                case 'STAR_DELETE':
                case 'STAR_SHOW':
                case 'POSTS':
                    if (is_object($this->currentUser)) {
                        if ($userEntity->getId() == $this->currentUser->getId()) {
                            return VoterInterface::ACCESS_GRANTED;
                        }
                    }
                    break;
                case 'STAR_NEW':
                    if (is_object($this->currentUser)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
            }

            return VoterInterface::ACCESS_DENIED;
        }
    }
}
