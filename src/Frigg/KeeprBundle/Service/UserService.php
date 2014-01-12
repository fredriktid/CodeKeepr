<?php

namespace Frigg\KeeprBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

class UserService extends ParentServiceAbstract implements UserServiceInterface
{
    protected $securityContext = null;
    protected $currentUser = null;

    public function __construct(EntityManager $em, SecurityContextInterface $securityContext, $configFile)
    {
        parent::__construct($em, $configFile);
        $this->securityContext = $securityContext;
        $this->currentUser = $this->getCurrentUser();
    }

    public function loadEntityBy($attributes)
    {
        $this->entity = $em->getRepository('FriggKeeprBundle:Tag')->findOneBy($attributes);
        return $this;
    }

    public function getEntityId()
    {
        return (is_object($this->entity)) ? $this->entity->getId() : 0;
    }

    public function loadEntityById($id)
    {
        $this->entity = $this->em->getRepository('FriggKeeprBundle:User')->findOneById($id);
        return $this;
    }

    public function getCurrentUser()
    {
        $token = $this->securityContext->getToken();
        return (is_object($token)) ? $token->getUser() : null;
    }

    public function getCurrentUserId()
    {
        return (is_object($this->currentUser)) ? $this->currentUser->getId() : 0;
    }

    public function generateUsername()
    {
        if (!is_object($this->entity)) {
            return null;
        }

        $username = array();
        $stopChars = array('.', '-', '_', '@', '+');
        foreach (str_split($this->entity->getEmail()) as $char) {
            if (in_array($char, $stopChars)) {
                break;
            }
            $username[] = $char;
        }

        return implode($username);
    }
}
