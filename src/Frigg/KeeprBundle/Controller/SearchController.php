<?php

namespace Frigg\KeeprBundle\Controller;

use Elastica\Filter\Range;
use Frigg\KeeprBundle\Entity\Tag;
use Frigg\KeeprBundle\Entity\User;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
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
        $queryString = $this->get('request')->query->get('query', '');
        $currentPage = $this->get('request')->query->get('page', 1);

        return [
            'title' => $this->get('translator')->trans('Home'),
            'query_text' => $queryString,
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
     * @Route("/posts", name="search_posts")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:posts.html.twig")
     */
    public function postsAction()
    {
        $queryString = $this->get('request')->query->get('query');
        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $wildCardString = (!$queryString) ? '*' : sprintf('*%s*', $queryString);
        $stringQuery = new Query\QueryString();
        $stringQuery->setQuery($wildCardString);

        $query = new Query();
        $query->setSort(['created_at' => ['order' => 'desc']])
            ->setQuery($stringQuery)
            ->setSize(99999);

        $entries = $this->get('fos_elastica.finder.website.post')
            ->find($query);

        /** @var SlidingPagination $pager */
        $pager = $this->get('knp_paginator')->paginate(
            $entries,
            $currentPage,
            $pageLimit
        );

        $pager->setUsedRoute('search_index');
        $pager->setParam('query', (strlen($queryString)) ? $queryString : '*');
        $pager->setParam('page', $currentPage);

        return [
            'entries' => $pager
        ];
    }

    /**
     * Posts by date
     *
     * @Route("/date", name="search_date")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:view.html.twig")
     */
    public function dateAction()
    {
        $query = $this->get('request')->query->get('query', 0);
        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $dateTs = strtotime($query);
        $dateFormat = date('Y-m-d', $dateTs);

        $stringQuery = new Query\QueryString();
        $stringQuery->setQuery('*');

        $rangeLower = new Query\Filtered($stringQuery, new Range('created_at', [
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
        $pager->setParam('query', $query);
        $pager->setParam('page', $currentPage);

        return [
            'title' => $dateFormat,
            'entries' => $pager
        ];
    }

    /**
     * Posts by tag
     *
     * @Route("/tag", name="search_tag")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:view.html.twig")
     */
    public function tagAction()
    {
        $query = $this->get('request')->query->get('query', 0);
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var Tag $tagEntity */
        if (!$tagEntity = $em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($query)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find tag')
            );
        }

        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $matchQuery = new Query\Match();
        $matchQuery->setField('Tags.name', $tagEntity->getName());

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust($matchQuery);

        $nestedQuery = new Query\Nested();
        $nestedQuery->setPath('Tags');
        $nestedQuery->setQuery($boolQuery);

        $query = new Query($nestedQuery);
        $query->setSize(99999);
        $query->setSort(['created_at' => ['order' => 'desc']]);

        $entries = $this->get('fos_elastica.finder.website.post')
            ->find($query);

        /** @var SlidingPagination $pager */
        $pager = $this->get('knp_paginator')->paginate(
            $entries,
            $currentPage,
            $pageLimit
        );

        $pager->setUsedRoute('search_tag');
        $pager->setParam('query', $query);
        $pager->setParam('page', $currentPage);

        return [
            'title' => $tagEntity->getName(),
            'entries' => $pager
        ];
    }

    /**
     * Posts by tag
     *
     * @Route("/user", name="search_user")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:view.html.twig")
     */
    public function userAction()
    {
        $query = (int) $this->get('request')->query->get('query');
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var User $userEntity */
        if (!$userEntity = $em->getRepository('FriggKeeprBundle:User')->findOneById($query)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find user')
            );
        }

        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $matchQuery = new Query\Match();
        $matchQuery->setField('User.id', $userEntity->getId());

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust($matchQuery);

        $query = new Query($boolQuery);
        $query->setSize(99999);
        $query->setSort(['created_at' => ['order' => 'desc']]);

        $entries = $this->get('fos_elastica.finder.website.post')
            ->find($query);

        /** @var SlidingPagination $pager */
        $pager = $this->get('knp_paginator')->paginate(
            $entries,
            $currentPage,
            $pageLimit
        );

        $pager->setUsedRoute('search_user');
        $pager->setParam('query', $query);
        $pager->setParam('page', $currentPage);

        return [
            'title' => $userEntity->getUsername(),
            'entries' => $pager
        ];
    }

    /**
     * jQuery Autocomplete
     *
     * @Route("/autocomplete", name="search_autocomplete")
     * @Method("GET")
     */
    public function autocompleteAction()
    {
        $query = $this->get('request')->query->get('query', '');
        $method = $this->get('request')->query->get('method', 'json');
        $type = $this->get('request')->query->get('type', 'post');

        $collection = array();
        if ($query) {
            $finder = $this->get('fos_elastica.finder.website.' . $type);
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
