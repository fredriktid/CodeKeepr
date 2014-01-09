<?php

namespace Frigg\KeeprBundle\Twig;

class UserExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('email_to_username', array($this, 'emailToUsername')),
        );
    }

    public function emailToUsername($email)
    {
        $username = array();
        foreach (str_split($email) as $char) {
            if (in_array($char, array('.','-','_','@'))) {
                break;
            }
            $username[] = $char;
        }

        return implode($username);
    }

    public function getName()
    {
        return 'user_extension';
    }
}

