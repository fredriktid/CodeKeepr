<?php

namespace Frigg\KeeprBundle\Security\Voter;

use Doctrine\ORM\EntityManager;

class BaseVoter
{
    protected $currentUser;
    protected $currentUserRoles;

    public function __construct()
    {
        $this->currentUser = null;
        $this->currentUserRoles = [];
    }

    protected function loadRolesFromToken($token)
    {
        $this->currentUser = $token->getUser();
        foreach ($token->getRoles() as $role) {
           $this->currentUserRoles[] = $role;
        }
    }

    protected function className($entity)
    {
        $classChunks = explode('\\', $entity);
        return array_pop($classChunks);
    }

    public function supportsClass($entity)
    {
        return 0 === strpos($this->className(get_called_class()), $this->className(get_class($entity)));
    }
}
