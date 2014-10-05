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
        $postFinder = $this->get('fos_elastica.finder.website.post');
        $postService  = $this->get('codekeepr.post.service');
        $query = $this->get('request')->query->get('query');
        $limit = $postService->getConfig('page_limit');
        $page = $this->get('request')->query->get('page', 1);

        $posts = array();
        if ($query) {
            $paginator = $this->get('knp_paginator');
            $posts = $paginator->paginate(
                $postFinder->createPaginatorAdapter($query),
                $page,
                $limit
            );
        }

        return array(
            'query' => $query,
            'posts' => $posts,
            'limit' => $limit,
            'title' => $this->get('translator')->trans(
                'Search: "query"',
                array('query' => $query)
            )
        );
    }

    /**
     * Search form
     *
     * @Route("/", name="search")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:form.html.twig")
     */
    public function formAction()
    {
        return array();
    }
}
