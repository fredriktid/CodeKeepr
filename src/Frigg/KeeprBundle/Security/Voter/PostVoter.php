<?php

namespace Frigg\KeeprBundle\Security\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Doctrine\ORM\EntityManager;

class PostVoter extends BaseVoter implements VoterInterface
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, 'POST_');
    }

    public function vote(TokenInterface $token, $postEntity, array $attributes)
    {
        if (!$this->supportsClass($postEntity)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $this->setCurrentUser($token);

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }
            switch ($this->securedArea($attribute)) {
                case 'STAR':
                case 'STAR_NEW':
                case 'STAR_REMOVE':
                case 'NEW':
                    if (is_object($this->currentUser)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
                case 'DELETE':
                case 'EDIT':
                    if (is_object($this->currentUser)) {
                        if ($this->currentUser->getId() == $postEntity->getUser()->getId()) {
                            return VoterInterface::ACCESS_GRANTED;
                        }
                    }
                    break;
                case 'SHOW':
                    if ($postEntity->isPublic()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }

                    if (is_object($this->currentUser)) {
                        if ($this->currentUser->getId() == $postEntity->getUser()->getId()) {
                            return VoterInterface::ACCESS_GRANTED;
                        }
                    }
                    break;
            }

            return VoterInterface::ACCESS_DENIED;
        }
    }
}
