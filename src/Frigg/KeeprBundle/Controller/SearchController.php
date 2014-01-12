<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Search controller.
 *
 * @Route("/search")
 */
class SearchController extends Controller
{
    /**
     * Perform search with Elastica
     *
     * @Route("/", name="search")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function viewAction()
    {
        $postService  = $this->get('codekeepr.service.post');
        $query = $this->get('request')->query->get('query');

        $collection = array();
        if ($query && strlen($query) > $postService->getConfig('search_minimum_chars')) {
            $paginator = $this->get('knp_paginator');
            $collection = $paginator->paginate(
                $postService->getFinder()->createPaginatorAdapter($query),
                $this->get('request')->query->get('page', 1),
                $postService->getConfig('page_limit')
            );
        }

        return array(
            'collection' => $collection,
            'limit' => $postService->getConfig('page_limit'),
            'title' => $this->get('translator')->trans(
                'Search: "query"',
                array('query' => $query)
            )
        );
    }
}
