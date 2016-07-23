<?php

namespace Frigg\KeeprBundle\Controller;

use Elastica\Filter\Range;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
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
     * Main search page
     *
     * @Route("/", name="search_index")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:index.html.twig")
     */
    public function indexAction()
    {
        $queryText = $this->get('request')->query->get('query', '*');
        $currentPage = $this->get('request')->query->get('page', 1);

        return [
            'title' => $this->get('translator')->trans('Home'),
            'query_text' => $queryText,
            'current_page' => $currentPage
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
     * @Template("FriggKeeprBundle:Search:list.html.twig")
     */
    public function listAction($type)
    {
        $queryText = $this->get('request')->query->get('query', '');
        $currentPage = $this->get('request')->query->get('page', 1);

        $queryString = new Query\QueryString();
        $queryString->setQuery(sprintf('*%s*', $queryText));

        $query = new Query();
        $query->setSort(['created_at' => ['order' => 'desc']])
            ->setQuery($queryString)
            ->setSize(99999);

        $pageLimit = $this->getParameter('codekeepr.page.limit');

        /** @var PaginatedFinderInterface $finder */
        $finder = $this->get(sprintf('fos_elastica.finder.website.%s', $type));
        $entries = $finder->find($query);

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');

        /** @var SlidingPagination $pager */
        $pager = $paginator->paginate(
            $entries,
            $currentPage,
            $pageLimit
        );

        $pager->setUsedRoute('search_index');
        $pager->setParam('query', (strlen($queryText)) ? $queryText : '*');
        $pager->setParam('page', $currentPage);

        return [
            'entries' => $pager,
        ];
    }

    /**
     * Posts by date
     *
     * @Route("/date/{dateString}", name="search_date")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:view.html.twig")
     */
    public function dateAction($dateString)
    {
        $dateTs = strtotime($dateString);
        $queryText = $this->get('request')->query->get('query', '*');
        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $queryString = new Query\QueryString();
        $queryString->setQuery($queryText);

        $rangeLower = new Query\Filtered($queryString, new Range('created_at', [
            'gte' => date('Y-m-d', $dateTs)
        ]));

        $rangeHigher = new Query\Filtered($rangeLower, new Range('created_at', [
            'lte' => date('Y-m-d', strtotime('+1 day', $dateTs))
        ]));

        $query = new Query();
        $query->setQuery($rangeHigher);
        $query->setSize(99999);
        $query->setSort([
            'created_at' => ['order' => 'desc']]
        );

        /** @var PaginatedFinderInterface $finder */
        $finder = $this->get('fos_elastica.finder.website.post');
        $entries = $finder->find($query);

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');

        /** @var SlidingPagination $pager */
        $pager = $paginator->paginate(
            $entries,
            $currentPage,
            $pageLimit
        );

        $pager->setUsedRoute('search_date');
        $pager->setParam('dateString', $dateString);
        $pager->setParam('query', $queryText);
        $pager->setParam('page', $currentPage);

        return [
            'title' => date('Y-m-d', $dateTs),
            'entries' => $pager
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
                    'url' => $this->generateUrl('search_index', [
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
