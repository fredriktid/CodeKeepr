<?php

namespace Frigg\KeeprBundle\Controller;

use Frigg\KeeprBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\Request;
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
     * Tagged posts.
     *
     * @Route("/{identifier}", name="tag_posts")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function showAction($identifier)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var Tag $tagEntity */
        if (!$tagEntity = $em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($identifier)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find tag')
            );
        }

        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $publicPosts = $em->getRepository('FriggKeeprBundle:Post')
            ->findPublicByTag($tagEntity);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $publicPosts,
            $currentPage,
            $pageLimit
        );

        return [
            'title' => $tagEntity->getName(),
            'tag_identifier' => $tagEntity->getIdentifier(),
            'posts' => $pagination,
            'query_text' => $identifier,
            'current_page' => $currentPage
        ];
    }
}
