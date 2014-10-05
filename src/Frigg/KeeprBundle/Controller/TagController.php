<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Tag controller.
 *
 * @Route("/tag")
 */
class TagController extends Controller
{


    /**
     * Tagged posts
     *
     * @Route("/", name="post_tags")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function indexAction()
    {
        return [
            'title' => 'Tags'
        ];
    }

    /**
     * Tagged posts
     *
     * @Route("/{identifier}", name="post_tag")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function showAction($identifier)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$tag = $em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($identifier)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find tag')
            );
        }

        $postService = $this->get('codekeepr.post.service');
        $posts = $postService->loadByTag($tag);
        $limit = $postService->getConfig('page_limit');
        $page = $this->get('request')->query->get('page', 1);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $posts,
            $page,
            $limit
        );

        return [
            'tag_identifier' => $identifier,
            'posts' => $pagination,
            'title' => $tag->getName()
        ];
    }

    /**
     * Sidebar template
     *
     * @Route("/sidebar", name="post_tag_sidebar")
     * @Method("GET")
     * @Template()
     */
    public function sidebarAction()
    {
        $postService = $this->get('codekeepr.post.service');
        $tags = $postService->loadPopularTags();

        return [
            'current_identifier' => '',
            'tags' => $tags
        ];
    }
}
