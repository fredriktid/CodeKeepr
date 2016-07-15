<?php

namespace Frigg\KeeprBundle\Twig;

use Doctrine\ORM\EntityManager;
use Frigg\KeeprBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class UserExtension.
 */
class UserExtension extends \Twig_Extension
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
        return 'user_extension';
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('generate_username', [$this, 'generateUsername']),
        ];
    }

    /**
     * @param $email
     *
     * @return mixed
     */
    public function generateUsername($email)
    {
        $user = new User();

        return $user->generateUsername($email);
    }
}
