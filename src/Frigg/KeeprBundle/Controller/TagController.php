<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Frigg\KeeprBundle\Entity\Tag;

/**
 * Tag controller.
 *
 * @Route("/tag")
 */
class TagController extends Controller
{
    /**
     * Lists all Tag entities.
     *
     * @Route("/", name="tag")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $userService = $this->get('codekeepr.service.user');
        $tagService = $this->get('codekeepr.service.tag');
        $tagService->setUserService($userService);
        $tagService->loadPopularTags($tagService->getConfig('tag_cloud_limit'));

        return array(
            'title' => $this->get('translator')->trans('Tags'),
            'collection' => $tagService->getCollection(),
            'collection_share' => $tagService->getCloudPecentages()
        );
    }

    /**
     * Groups and ranks all tags
     *
     * @Route("/group/{currentIdentifier}", name="tag_group", defaults={"currentIdentifier" = null})
     * @Method("GET")
     * @Template()
     */
    public function groupAction(Request $request, $currentIdentifier = null)
    {
        $userService = $this->get('codekeepr.service.user');
        $tagService = $this->get('codekeepr.service.tag');
        $tagService->setUserService($userService);
        $tagService->loadPopularTags();

        return array(
            'current_identifier' => $currentIdentifier,
            'collection' => $tagService->getCollection()
        );
    }

    /**
     * Finds and displays a Tag entity.
     *
     * @Route("/{identifier}", name="tag_show")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function showAction($identifier)
    {
        $tagService = $this->get('codekeepr.service.tag');
        $tagService->loadEntityByIdentifier($identifier);

        if (!$tagService->getEntity()) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find tag')
            );
        }

        $userService = $this->get('codekeepr.service.user');
        $tagService->setUserService($userService);
        $tagService->loadPostsByTag();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $tagService->getCollection(),
            $this->get('request')->query->get('page', 1),
            $tagService->getConfig('page_limit')
        );

        return array(
            'tag_identifier' => $identifier,
            'collection' => $pagination,
            'title' => $tagService->getEntity()->getName()
        );
    }

    /**
     * Search for tag, returns json
     *
     * @Route("/search/json", name="tag_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction()
    {
        $tagService = $this->get('codekeepr.service.tag');
        $query = $this->get('request')->query->get('query');
        $method = $this->get('request')->query->get('method');

        $collection = array();
        if ($query && strlen($query) >= $tagService->getConfig('search_minimum_chars')) {
            $tagSearch = $tagService->getFinder()->find($query.'*', $tagService->getConfig('tag_autocomplete_limit'));
            foreach($tagSearch as $tag) {
                $collection[] = array(
                    'label' => $tag->getName(),
                    'value' => $tag->getName()
                );
            }
        }

        switch ($method) {
            case 'json':
                $response = new Response(json_encode($collection));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            default:
                echo 'Method not supported'; die;
        }
    }
}
