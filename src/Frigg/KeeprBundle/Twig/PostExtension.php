<?php

namespace Frigg\KeeprBundle\Twig;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PostExtension extends \Twig_Extension
{
    private $entityManager;
    private $securityContext;

    public function __construct(EntityManager $entityManager, SecurityContextInterface $securityContext)
    {
        $this->entityManager = $entityManager;
        $this->securityContext = $securityContext;
    }

    public function getName()
    {
        return 'post_extension';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('is_starred', [$this, 'isStarred'])
        ];
    }

    public function isStarred($postEntity)
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            return false;
        }

        $securityToken = $this->securityContext->getToken();
        $currentUserEntity = $securityToken->getUser();

        return $this->entityManager->getRepository('FriggKeeprBundle:Star')->isStarred(
            $postEntity,
            $currentUserEntity
        );
    }
}
