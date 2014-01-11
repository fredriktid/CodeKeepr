<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Frigg\KeeprBundle\Entity\Post;
use Frigg\KeeprBundle\Entity\Tag;

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

        // todo: a real voter goes here...
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl(
                'fos_user_security_login'
            ));
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
     * @Template()
     */
    public function starAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$userEntity = $em->getRepository('FriggKeeprBundle:User')->find($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find user')
            );
        }

        // temporary, will be replaced by a real voter
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl(
                'fos_user_security_login'
            ));
        }

        // soon...

        return array(
            'entity' => $userEntity,
            'collection' => array(),
            'title' => 'Starred'
        );
    }
}
