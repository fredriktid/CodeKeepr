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

        $publicPosts = $em->getRepository('FriggKeeprBundle:Post')->loadByUser($userEntity);
        $currentPage = $this->get('request')->query->get('page', 1);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $publicPosts,
            $currentPage,
            20
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

        $privatePosts = $em->getRepository('FriggKeeprBundle:Post')->loadPrivate($userEntity);
        $currentPage = $this->get('request')->query->get('page', 1);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $privatePosts,
            $currentPage,
            20
        );

        return [
            'posts' => $pagination,
            'title' => $this->get('translator')->trans(
                'My private posts'
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

        $publicPosts = $em->getRepository('FriggKeeprBundle:Post')->loadStarred($userEntity);
        $currentPage = $this->get('request')->query->get('page', 1);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $publicPosts,
            $currentPage,
            20
        );

        return [
            'posts' => $pagination,
            'title' => $this->get('translator')->trans(
                'My starred posts'
            )
        ];
    }
}
