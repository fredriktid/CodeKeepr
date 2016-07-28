<?php

namespace Frigg\KeeprBundle\Controller;

use Elastica\Query;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\Nested;
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
     * Search and show list.
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

        $wildCardString = (!$queryString || $queryString === '*') ? '*' : sprintf('*%s*', $queryString);

        $queryBuilder = $this->get('codekeepr.elastica.query.builder')
            ->setQueryString($wildCardString)
            ->setNested($queryNested)
            ->setRanges($queryRanges)
            ->setObjects($queryObjects);

        $boolQuery = $queryBuilder
            ->setBoolQuery(new Query\Bool())
            ->mustQueryString()
            ->mustFilterNested()
            ->mustFilterObjects()
            ->mustFilterRange()
            ->getBoolQuery();

        $query = new Query($boolQuery);
        $query->setSize(99999)
            ->addSort('_score')
            ->addSort(['created_at' => ['order' => 'desc']]);

        $tagsAggreate = new Terms('Tags');
        $tagsAggreate
            ->setField('Tags.name.untouched')
            ->setSize(20);

        $nestedAggregate = new Nested('tags', 'Tags');
        $nestedAggregate->addAggregation($tagsAggreate);
        $query->addAggregation($nestedAggregate);

        $pageLimit = $this->getParameter('codekeepr.page.limit');
        $pager = $this->get('fos_elastica.finder.website.post')
            ->findPaginated($query)
            ->setCurrentPage($currentPage)
            ->setMaxPerPage($pageLimit);

        return [
            'pager' => $pager,
            'query' => $queryString,
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
