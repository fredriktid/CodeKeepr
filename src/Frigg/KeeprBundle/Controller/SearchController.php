<?php

namespace Frigg\KeeprBundle\Controller;

use Elastica\Query;
use Frigg\KeeprBundle\Elastica\Value\Aggregation as AggregationValue;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function indexAction(Request $request)
    {
        $queryString = $request->query->get('query', '');
        $queryNested = $request->query->get('nested', []);
        $queryRanges = $request->query->get('ranges', []);
        $queryObjects = $request->query->get('objects', []);
        $currentPage = $request->query->get('page', 1);

        return [
            'title' => $this->get('translator')->trans('Home'),
            'query_text' => $queryString,
            'current_page' => $currentPage,
            'query_nested' => $queryNested,
            'query_ranges' => $queryRanges,
            'query_objects' => $queryObjects
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
     * Search posts
     *
     * @param Request $request
     * @Route("/posts", name="search_posts")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Search:posts.html.twig")
     */
    public function postsAction(Request $request)
    {
        $queryString = $request->query->get('query');
        $queryNested = $request->query->get('nested', []);
        $queryRanges = $request->query->get('ranges', []);
        $queryObjects = $request->query->get('objects', []);
        $currentPage = $request->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $wildCardString = (!$queryString || $queryString === '*') ? '*' : sprintf('*%s*', $queryString);

        $queryBuilder = $this->get('codekeepr.elastica.query.builder')
            ->setQueryString($wildCardString)
            ->setSize(99999)
            ->addSortBy('_score')
            ->addSortBy(['created_at' => ['order' => 'desc']])
            ->setNested($queryNested)
            ->setRanges($queryRanges)
            ->setObjects($queryObjects)
            ->addAggregation('nested', new AggregationValue([
                'field' => 'Tags.name.untouched',
                'size' => 20
            ]));

        $query = $queryBuilder
            ->setBoolQuery(new Query\BoolQuery())
            ->mustQueryString()
            ->mustFilterNested()
            ->mustMatchObjects()
            ->mustFilterRange()
            ->buildQuery();

        $pager = $this->get('fos_elastica.finder.website.post')
            ->findPaginated($query)
            ->setCurrentPage($currentPage)
            ->setMaxPerPage($pageLimit);

        return [
            'pager' => $pager,
            'query' => $queryString,
            'ranges' => $queryRanges,
            'nested' => $queryNested,
            'objects' => $queryObjects,
            'current_page' => $currentPage
        ];
    }

    /**
     * jQuery Autocomplete
     *
     * @Route("/autocomplete", name="search_autocomplete")
     * @Method("GET")
     */
    public function autocompleteAction(Request $request)
    {
        $query = $request->query->get('query', '');
        $method = $request->query->get('method', 'json');
        $type = $request->query->get('type', 'post');

        $collection = [];
        if ($query) {
            $finder = $this->get('fos_elastica.finder.website.' . $type);
            $results = $finder->find($query.'*', 5);
            foreach ($results as $object) {
                $collection[] = [
                    'label' => $object->__toString(),
                    'url' => $this->generateUrl('search_index', [
                        'query' => $object->__toString(),
                    ])
                ];
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
