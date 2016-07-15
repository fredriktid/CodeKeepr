<?php

namespace Frigg\KeeprBundle\Security\Voter;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Class BaseVoter.
 */
class BaseVoter
{
    /**
     * @var UserInterface
     */
    protected $currentUser;

    /**
     * @var array
     */
    protected $currentUserRoles;

    /**
     * BaseVoter constructor.
     */
    public function __construct()
    {
        $this->currentUser = null;
        $this->currentUserRoles = [];
    }

    /**
     * @param AbstractToken $token
     */
    protected function loadRolesFromToken($token)
    {
        /* @var UserInterface currentUser */
        $this->currentUser = $token->getUser();
        foreach ($token->getRoles() as $role) {
            $this->currentUserRoles[] = $role;
        }
    }

    /**
     * @param $entity
     *
     * @return mixed
     */
    protected function className($entity)
    {
        $classChunks = explode('\\', $entity);

        return array_pop($classChunks);
    }

    /**
     * @param $entity
     *
     * @return bool
     */
    public function supportsClass($entity)
    {
        return 0 === strpos($this->className(get_called_class()), $this->className(get_class($entity)));
    }
}
