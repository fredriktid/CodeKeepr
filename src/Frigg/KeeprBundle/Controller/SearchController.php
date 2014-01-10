<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Search controller.
 *
 * @Route("/search")
 */
class SearchController extends Controller {

    /**
     * Perform search with Elastica
     *
     * @Route("/", name="search")
     * @Method("GET")
     * @Template()
     */
    public function viewAction()
    {
        $query = $this->get('request')->query->get('query');
        $collection = null;
        if (strlen($query) > 0) {
            $limit = 20;
            $finder = $this->get('fos_elastica.finder.website.post');
            $paginator = $this->get('knp_paginator');
            $collection = $paginator->paginate(
                $finder->createPaginatorAdapter($query),
                $this->get('request')->query->get('page', 1),
                $limit
            );
        }

        return array(
            'title' => $this->get('translator')->trans('Search'),
            'query' => $query,
            'collection' => $collection
        );
    }
}

