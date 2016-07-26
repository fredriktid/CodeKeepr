<?php

namespace Frigg\KeeprBundle\Twig;

use Doctrine\ORM\EntityManager;
use Frigg\KeeprBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class SearchExtension.
 */
class SearchExtension extends \Twig_Extension
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'search_extension';
    }

    /**
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('filter_remove', [$this, 'removeFromFilter']),
            new \Twig_SimpleFilter('filter_add', [$this, 'addToFilter']),
        ];
    }


    public function removeFromFilter($filter, $itemToRemove)
    {
        if (($index = array_search($itemToRemove, $filter)) !== false) {
            unset($filter[$index]);
        }

        return $filter;
    }

    public function addToFilter($filter, $itemToAdd)
    {
        $itemToAdd = (!is_array($itemToAdd)) ? [$itemToAdd] : $itemToAdd;
        return array_merge($filter, $itemToAdd);
    }
}
