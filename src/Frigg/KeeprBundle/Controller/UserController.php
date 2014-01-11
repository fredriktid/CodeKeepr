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
    public function postAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$userEntity = $em->getRepository('FriggKeeprBundle:User')->find($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find user')
            );
        }

        if (!$this->get('security.context')->isGranted('USER_POST', $userEntity)) {
            throw new AccessDeniedException;
        }

        $pageLimit = 20;
        $qb = $em->createQueryBuilder();
        $collection = $qb->select('p')
           ->from('FriggKeeprBundle:Post', 'p')
           ->leftJoin('p.User', 'u')
           ->where('u.id = :user_id')
           ->orderBy('p.created_at', 'DESC')
           ->setParameter('user_id', $userEntity->getId())
           ->getQuery()->getResult();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $collection,
            $this->get('request')->query->get('page', 1),
            $pageLimit
        );

        return array(
            'collection' => $pagination,
            'limit' => $pageLimit,
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
    public function starAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$userEntity = $em->getRepository('FriggKeeprBundle:User')->find($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find user')
            );
        }

        if (!$this->get('security.context')->isGranted('USER_STAR', $userEntity)) {
            throw new AccessDeniedException;
        }

        $pageLimit = 20;
        $qb = $em->createQueryBuilder();
        $collection = $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Stars', 's')
            ->where(
                $qb->expr()->eq('s.User', ':user_id')
            )
            ->orderBy('s.created_at', 'DESC')
            ->setParameters(array(
                'user_id' => $id
            ))
            ->getQuery()->getResult();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $collection,
            $this->get('request')->query->get('page', 1),
            $pageLimit
        );

        return array(
            'collection' => $pagination,
            'limit' => $pageLimit,
            'title' => $this->get('translator')->trans('My starred posts')
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
        $request = $this->get('request');
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
            if (!$this->get('security.context')->isGranted('USER_STAR', $userEntity)) {
                $session->getFlashBag()->add(
                    'error',
                    $translator->trans('Access denied')
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
        }

        if ($referer = $request->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirect($this->generateUrl(
            'home'
        ));
    }
}
