<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Response;

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
        $postService = $this->get('codekeepr.post.service');

        $query = $this->get('request')->query->get('query');
        $limit = $postService->getConfig('page_limit');
        $page = $this->get('request')->query->get('page', 1);

        $posts = [];
        if ($query) {
            $paginator = $this->get('knp_paginator');
            $posts = $paginator->paginate(
                $postFinder->createPaginatorAdapter($query),
                $page,
                $limit
            );
        }

        return [
            'query' => $query,
            'posts' => $posts,
            'limit' => $limit,
            'title' => $this->get('translator')->trans(
                'Search: "query"',
                ['query' => $query]
            )
        ];
    }

    /**
     * Search form
     *
     * @Route("/form/{query}", name="search_form", defaults={"query" = null})
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:form.html.twig")
     */
    public function formAction($query = null)
    {
        return [
            'query' => $query
        ];
    }

    /**
     * Search form
     *
     * @Route("/autocomplete/{type}", name="search_autocomplete", defaults={"type" = "post"})
     * @Method("GET")
     */
    public function autocompleteAction($type)
    {
        $query = $this->get('request')->query->get('query', '');
        $method = $this->get('request')->query->get('method', 'json');

        $collection = array();
        if ($query) {
            $finder = $this->get('fos_elastica.finder.website.' . $type);
            $results = $finder->find($query . '*', 5);
            foreach ($results as $object) {
                $collection[] = array(
                    'label' => $object->__toString()
                    'value' => $object->__toString()
                );
            }
        }

        switch ($method) {
            default:
            case 'json':
                $response = new Response(json_encode($collection));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
        }
    }
}

