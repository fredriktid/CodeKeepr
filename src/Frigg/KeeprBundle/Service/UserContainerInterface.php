<?php

namespace Frigg\KeeprBundle\Service;

interface UserContainerInterface
{
    public function getUserService();
    public function setUserService(UserServiceInterface $userService);
}
