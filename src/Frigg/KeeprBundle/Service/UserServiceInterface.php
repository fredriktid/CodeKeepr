<?php

namespace Frigg\KeeprBundle\Service;

interface UserServiceInterface
{
    public function getEntityId();
    public function getCurrentUser();
    public function getCurrentUserId();
    public function generateUsername();
}
