<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * Public posts by a user.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="user_posts")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function showAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        if (!$userEntity = $em->getRepository('FriggKeeprBundle:User')->find($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('User not found')
            );
        }

        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $publicPosts = $em->getRepository('FriggKeeprBundle:Post')
            ->findPublicByUser($userEntity);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $publicPosts,
            $currentPage,
            $pageLimit
        );

        return [
            'posts' => $pagination,
            'title' =>  $this->get('translator')->trans(
                'My posts'
            )
        ];
    }

    /**
     * Private posts by a user.
     *
     * @Route("/{id}/private", requirements={"id" = "\d+"}, name="user_private_posts")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function privateAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        if (!$userEntity = $em->getRepository('FriggKeeprBundle:User')->find($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('User not found')
            );
        }

        if (!$this->get('security.context')->isGranted('USER_PRIVATE_POSTS', $userEntity)) {
            throw new AccessDeniedException();
        }

        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $privatePosts = $em->getRepository('FriggKeeprBundle:Post')
            ->findPrivateByUser($userEntity);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $privatePosts,
            $currentPage,
            $pageLimit
        );

        return [
            'posts' => $pagination,
            'title' => $this->get('translator')->trans(
                'Private posts'
            )
        ];
    }

    /**
     * Starred post by user.
     *
     * @Route("/{id}/starred", name="user_starred_posts")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function userStarredAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        if (!$userEntity = $em->getRepository('FriggKeeprBundle:User')->find($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('User not found')
            );
        }

        if (!$this->get('security.context')->isGranted('USER_STAR_SHOW', $userEntity)) {
            throw new AccessDeniedException();
        }

        $currentPage = $this->get('request')->query->get('page', 1);
        $pageLimit = $this->getParameter('codekeepr.page.limit');

        $publicPosts = $em->getRepository('FriggKeeprBundle:Post')
            ->findStarredByUser($userEntity);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $publicPosts,
            $currentPage,
            $pageLimit
        );

        return [
            'posts' => $pagination,
            'title' => $this->get('translator')->trans(
                'Starred posts'
            )
        ];
    }
}
