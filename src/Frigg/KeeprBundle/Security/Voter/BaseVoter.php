<?php

namespace Frigg\KeeprBundle\Security\Voter;

use Doctrine\ORM\EntityManager;

class BaseVoter
{
    protected $em = null;
    protected $user = null;
    protected $roles = array();
    protected $attribute = null;

    public function __construct(EntityManager $em, $attribute)
    {
        $this->em = $em;
        $this->attribute = $attribute;
    }

    protected function setRole($role)
    {
        $this->roles[] = $role;
    }

    protected function setUserByToken($token)
    {
        $this->user = $token->getUser();

        $roles = array();
        if(count($token->getRoles())) {
            foreach ($token->getRoles() as $role) {
               $this->setRole($role->getRole());
            }
        }
    }

    protected function className($class)
    {
        $nameSplit = explode('\\', $class);
        return array_pop($nameSplit);
    }

    protected function securedArea($attribute)
    {
        return substr($attribute, strlen($this->attribute));
    }

    public function supportsClass($object)
    {
        return false !== strpos($this->className(get_called_class()), $this->className(get_class($object)));
    }

    public function supportsAttribute($attribute)
    {
        return 0 === strpos($attribute, $this->attribute);
    }
}
