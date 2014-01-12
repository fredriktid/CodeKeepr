<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Frigg\KeeprBundle\Entity\Post;
use Frigg\KeeprBundle\Entity\Tag;
use Frigg\KeeprBundle\Entity\Star;

/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * User posts
     *
     * @Route("/{id}/post", name="user_post")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function myPostsAction($id)
    {
        $userService = $this->get('codekeepr.service.user');
        $userService->loadEntityById($id);

        if (!$userService->getEntity()) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find user')
            );
        }

        if (!$this->get('security.context')->isGranted('USER_POSTS', $userService->getEntity())) {
            throw new AccessDeniedException;
        }

        $postService = $this->get('codekeepr.service.post');
        $postService->setUserService($userService);
        $postService->loadByUser();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $postService->getCollection(),
            $this->get('request')->query->get('page', 1),
            $postService->getConfig('page_limit')
        );

        return array(
            'collection' => $pagination,
            'title' => $this->get('translator')->trans('My posts')
        );
    }

    /**
     * Starred post by user
     *
     * @Route("/{id}/star", name="user_star")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function myStarsAction($id)
    {
        $userService = $this->get('codekeepr.service.user');
        $userService->loadEntityById($id);

        if (!$userService->getEntity()) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find user')
            );
        }

        if (!$this->get('security.context')->isGranted('USER_STAR_SHOW', $userService->getEntity())) {
            throw new AccessDeniedException;
        }

        $postService = $this->get('codekeepr.service.post');
        $postService->setUserService($userService);
        $postService->loadStarredByUser();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $postService->getCollection(),
            $this->get('request')->query->get('page', 1),
            $postService->getConfig('page_limit')
        );

        return array(
            'collection' => $pagination,
            'title' => $this->get('translator')->trans('Starred')
        );
    }

    /**
     * Add a new starred post
     *
     * @Route("/{id}/star/{postId}", requirements={"id" = "\d+", "postId" = "\d+"},  name="user_star_new")
     * @Method("GET")
     */
    public function newStarAction($id, $postId)
    {
        $session = $this->get('session');
        $translator = $this->get('translator');

        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('You must be logged in to star something')
            );
            throw new AccessDeniedException;
        }

        $em = $this->getDoctrine()->getManager();

        if (!$userEntity = $em->getRepository('FriggKeeprBundle:User')->find($id)) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('Unable to find user')
            );
        } else {
            $starEntity = $em->getRepository('FriggKeeprBundle:Star')->findOneBy(array(
                'User' => $id,
                'Post' => $postId
            ));

            if (!$starEntity) {
                if (!$postEntity = $em->getRepository('FriggKeeprBundle:Post')->find($postId)) {
                    $session->getFlashBag()->add(
                        'error',
                        $translator->trans('Unable to find post')
                    );
                } else {
                    $starEntity = new Star;
                    $starEntity->setUser($userEntity);
                    $starEntity->setPost($postEntity);
                    $em->persist($starEntity);
                    $em->flush();
                    $session->getFlashBag()->add(
                        'success',
                        $translator->trans(
                            'Added star on "topic"',
                            array('topic' => $postEntity->getTopic())
                        )
                    );
                }
            } else {
                $this->get('session')->getFlashBag()->add(
                    'info',
                    $translator->trans('You\'ve already starred this post')
                );
            }
        }

        if ($referer = $request = $this->get('request')->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirect($this->generateUrl(
            'home'
        ));
    }

    /**
     * Add a new starred post
     *
     * @Route("/{id}/star/{postId}/delete", requirements={"id" = "\d+", "postId" = "\d+"},  name="user_star_delete")
     * @Method("GET")
     */
    public function deleteStarAction($id, $postId)
    {
        $session = $this->get('session');
        $translator = $this->get('translator');

        $em = $this->getDoctrine()->getManager();
        $starEntity = $em->getRepository('FriggKeeprBundle:Star')->findOneBy(array(
            'User' => $id,
            'Post' => $postId
        ));

        if (!$starEntity) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('Unable to find star in database')
            );
        } else {
            $starPostEntity = $starEntity->getPost();
            $starUserEntity = $starEntity->getUser();
            if (!$this->get('security.context')->isGranted('USER_STAR_DELETE', $starUserEntity)) {
                $session->getFlashBag()->add(
                    'error',
                    $translator->trans('Access denied')
                );
            } else {
                $em->remove($starEntity);
                $em->flush();
                $session->getFlashBag()->add(
                    'notice',
                    $translator->trans(
                        'Unstarred "topic"',
                        array('topic' => $starPostEntity->getTopic())
                    )
                );
            }
        }

        if ($referer = $this->get('request')->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirect($this->generateUrl(
            'home'
        ));

    }
}
