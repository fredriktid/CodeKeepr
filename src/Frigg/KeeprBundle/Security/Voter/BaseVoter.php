<?php

namespace Frigg\KeeprBundle\Security\Voter;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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
     * @param TokenInterface $token
     */
    protected function loadRolesFromToken(TokenInterface $token)
    {
        $this->currentUser = $token->getUser();

        if (!($token instanceof AbstractToken)) {
            return;
        }

        foreach ($token->getRoles() as $role) {
            $this->currentUserRoles[] = $role;
        }
    }

    /**
     * @param $entity
     * @return mixed
     */
    protected function className($entity)
    {
        $classChunks = explode('\\', $entity);

        return array_pop($classChunks);
    }

    /**
     * @param $entity
     * @return bool
     */
    public function supportsClass($entity)
    {
        return 0 === strpos($this->className(get_called_class()), $this->className(get_class($entity)));
    }

    /**
     * @param int $userId
     * @return bool
     */
    protected function isOwnedByUserId($userId)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        return $this->currentUser->getId() == $userId;
    }

    /**
     * @return bool
     */
    protected function isLoggedIn()
    {
        return is_object($this->currentUser);
    }
}
