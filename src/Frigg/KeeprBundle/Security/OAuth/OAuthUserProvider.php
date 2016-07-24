<?php

namespace Frigg\KeeprBundle\Security\OAuth;

use Frigg\KeeprBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;

/**
 * Class OAuthUserProviderService
 * @package Frigg\KeeprBundle\Service
 */
class OAuthUserProvider extends FOSUBUserProvider
{
    /**
     * @param UserResponseInterface $response
     * @return \FOS\UserBundle\Model\UserInterface
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $serviceName = $response->getResourceOwner()->getName();

        $email = $response->getEmail();
        if (null === $email) {
            $email = preg_replace('/\s+/', '', sprintf(
                '%s@%s', $response->getNickname(), $serviceName)
            );
        }

        /** @var User $user */
        $user = $this->userManager->findUserBy(
            ['email' => $email]
        );

        if (null === $user) {
            $user = new User();
            $user->setEmail($email);
            $user->setPassword(substr(str_shuffle(md5(time())), 0, 10));
            $user->setEnabled(true);
            $user->setRoles(['ROLE_USER']);
        }

        $setter = sprintf('set%sAccessToken', ucfirst($serviceName));
        $user->$setter($response->getAccessToken());

        $setter = sprintf('set%sId', ucfirst($serviceName));
        $user->$setter($response->getNickname());

        $this->userManager->updateUser($user, true);

        return $user;
    }
}
