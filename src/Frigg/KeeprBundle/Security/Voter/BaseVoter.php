<?php

namespace Frigg\KeeprBundle\Security\Voter;

use Doctrine\ORM\EntityManager;

class BaseVoter
{
    protected $em = null;
    protected $roles = array();
    protected $attribute = null;
    protected $currentUser = null;

    public function __construct(EntityManager $em, $attribute)
    {
        $this->em = $em;
        $this->attribute = $attribute;
    }

    protected function setRole($role)
    {
        $this->roles[] = $role;
    }

    protected function setCurrentUser($token)
    {
        $this->currentUser = $token->getUser();

        $roles = array();
        if(count($token->getRoles())) {
            foreach ($token->getRoles() as $role) {
               $this->setRole($role->getRole());
            }
        }
    }

    protected function className($entity)
    {
        $classChunks = explode('\\', $entity);
        return array_pop($classChunks);
    }

    protected function securedArea($attribute)
    {
        return substr($attribute, strlen($this->attribute));
    }

    public function supportsClass($entity)
    {
        return 0 === strpos($this->className(get_called_class()), $this->className(get_class($entity)));
    }

    public function supportsAttribute($attribute)
    {
        return 0 === strpos($attribute, $this->attribute);
    }
}
