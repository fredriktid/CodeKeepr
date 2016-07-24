<?php

namespace Frigg\KeeprBundle\Controller;

use Elastica\Filter\Range;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Frigg\KeeprBundle\Entity\Tag;
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
            'query' => $query
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
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $queryString = new Query\QueryString();
        $queryString->setQuery(sprintf('*%s*', $queryText));

        $query = new Query();
        $query->setSort(['created_at' => ['order' => 'desc']])
            ->setQuery($queryString)
            ->setSize(99999);

        $entries = $this->get(sprintf('fos_elastica.finder.website.%s', $type))
            ->find($query);

        /** @var SlidingPagination $pager */
        $pager = $this->get('knp_paginator')->paginate(
            $entries,
            $currentPage,
            $pageLimit
        );

        $pager->setUsedRoute('search_index');
        $pager->setParam('query', (strlen($queryText)) ? $queryText : '*');
        $pager->setParam('page', $currentPage);

        return [
            'entries' => $pager
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
        $dateFormat = date('Y-m-d', $dateTs);

        $queryText = $this->get('request')->query->get('query', '*');
        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $queryString = new Query\QueryString();
        $queryString->setQuery($queryText);

        $rangeLower = new Query\Filtered($queryString, new Range('created_at', [
            'gte' => $dateFormat
        ]));

        $rangeHigher = new Query\Filtered($rangeLower, new Range('created_at', [
            'lte' => $dateFormat
        ]));

        $query = new Query();
        $query->setQuery($rangeHigher);
        $query->setSize(99999);
        $query->setSort([
            'created_at' => ['order' => 'desc']]
        );

        $entries = $this->get('fos_elastica.finder.website.post')
            ->find($query);

        /** @var SlidingPagination $pager */
        $pager = $this->get('knp_paginator')->paginate(
            $entries,
            $currentPage,
            $pageLimit
        );

        $pager->setUsedRoute('search_date');
        $pager->setParam('dateString', $dateString);
        $pager->setParam('query', $queryText);
        $pager->setParam('page', $currentPage);

        return [
            'title' => $dateFormat,
            'entries' => $pager
        ];
    }

    /**
     * Posts by tag
     *
     * @Route("/tag/{tag}", name="search_tag")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:view.html.twig")
     */
    public function tagAction($tag)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var Tag $tagEntity */
        if (!$tagEntity = $em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($tag)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find tag')
            );
        }

        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $tagsQuery = new Query\Terms();
        $tagsQuery->setTerms('Tags', [$tagEntity->getId()]);

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addShould($tagsQuery);

        $entries = $this->get('fos_elastica.finder.website.post')
            ->find($boolQuery);

        /** @var SlidingPagination $pager */
        $pager = $this->get('knp_paginator')->paginate(
            $entries,
            $currentPage,
            $pageLimit
        );

        $pager->setUsedRoute('search_date');
        $pager->setParam('tag', $tag);
        $pager->setParam('page', $currentPage);

        return [
            'title' => $tagEntity->getIdentifier(),
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
