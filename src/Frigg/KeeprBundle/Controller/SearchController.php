<?php

namespace Frigg\KeeprBundle\Controller;

use Elastica\Filter\Range;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Elastica\Query;

/**
 * Search controller.
 *
 * @Route("/search")
 */
class SearchController extends Controller
{
    /**
     * Perform search with Elastica.
     *
     * @Route("/", name="search")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function viewAction()
    {
        $postFinder = $this->get('fos_elastica.finder.website.post');
        $query = $this->get('request')->query->get('query');
        $page = $this->get('request')->query->get('page', 1);
        $limit = 20;

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
            ),
        ];
    }

    /**
     * Search form.
     *
     * @Route("/form/{query}", name="search_form", defaults={"query" = null})
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:form.html.twig")
     */
    public function formAction($query = null)
    {
        return [
            'query' => $query,
        ];
    }

    /**
     * Search and show list.
     *
     * @Route("/list/{type}", name="search_list", defaults={"type" = "post"})
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:list.html.twig")
     */
    public function listAction($type)
    {
        $queryText = $this->get('request')->query->get('query', '*') ?: '*';
        $currentPage = $this->get('request')->query->get('page', 1);

        $queryString = new Query\QueryString();
        $queryString->setQuery($queryText);

        $query = new Query();
        $query->setSort(['created_at' => ['order' => 'desc']]);
        $query->setQuery($queryString);
        $query->setSize(99999);

        $finder = $this->get('fos_elastica.finder.website.' . $type);
        $results = $finder->find($query);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $results,
            $currentPage,
            20
        );

        return [
            'posts' => $pagination,
            'current_page' => $currentPage,
            'query_text' => $queryText,
        ];
    }

    /**
     * Posts by date
     *
     * @Route("/date/{dateString}", name="search_date")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:list.html.twig")
     */
    public function dateAction($dateString)
    {
        $queryText = $this->get('request')->query->get('query', '*');
        $currentPage = $this->get('request')->query->get('page', 1);

        $queryString = new Query\QueryString();
        $queryString->setQuery($queryText);

        $rangeLower = new Query\Filtered($queryString, new Range('created_at', [
            'gte' => date('Y-m-d H:i:s', strtotime($dateString))
        ]));

        $rangeHigher = new Query\Filtered($rangeLower, new Range('created_at', [
            'lte' => date('Y-m-d H:i:s', strtotime($dateString))
        ]));

        $query = new Query();
        $query->setSort(['created_at' => ['order' => 'desc']]);
        $query->setQuery($rangeHigher);
        $query->setSize(99999);

        $finder = $this->get('fos_elastica.finder.website.post');
        $results = $finder->find($query);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $results,
            $currentPage,
            20
        );

        return [
            'posts' => $pagination,
            'current_page' => $currentPage,
            'query_text' => $queryText,
        ];
    }

    /**
     * jQuery Autocomplete
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
            $finder = $this->get('fos_elastica.finder.website.'.$type);
            $results = $finder->find($query.'*', 5);
            foreach ($results as $object) {
                $collection[] = array(
                    'label' => $object->__toString(),
                    'url' => $this->generateUrl('search', [
                        'query' => $object->__toString(),
                    ]),
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
