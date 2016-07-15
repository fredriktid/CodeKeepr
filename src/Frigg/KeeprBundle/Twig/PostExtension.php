<?php

namespace Frigg\KeeprBundle\Twig;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class PostExtension.
 */
class PostExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * PostExtension constructor.
     *
     * @param EntityManager            $entityManager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(EntityManager $entityManager, SecurityContextInterface $securityContext)
    {
        $this->entityManager = $entityManager;
        $this->securityContext = $securityContext;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'post_extension';
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('is_starred', [$this, 'isStarred']),
            new \Twig_SimpleFilter('comment_count', [$this, 'commentCount']),
        ];
    }

    /**
     * @param $postEntity
     *
     * @return mixed
     */
    public function commentCount($postEntity)
    {
        return $this->entityManager->getRepository('FriggKeeprBundle:Comment')->postCount($postEntity);
    }

    /**
     * @param $postEntity
     *
     * @return bool|null
     */
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
