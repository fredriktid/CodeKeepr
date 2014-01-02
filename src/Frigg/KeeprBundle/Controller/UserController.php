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
use Frigg\KeeprBundle\Form\PostType;

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
     * @Route("/{id}", name="user_post")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        // voter goes here...
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl(
                'fos_user_security_login'
            ));
        }

        $em = $this->getDoctrine()->getManager();
        $userEntity = $em->getRepository('FriggKeeprBundle:User')->find($id);
        if (!$userEntity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $limit = 20;
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
            $limit
        );

        return array(
            'entity' => $userEntity,
            'collection' => $pagination,
            'title' => 'User posts'
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
        // temporary, will be replaced by a real voter
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl(
                'fos_user_security_login'
            ));
        }

        $em = $this->getDoctrine()->getManager();
        $userEntity = $em->getRepository('FriggKeeprBundle:User')->find($id);
        if (!$userEntity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        // soon...

        return array(
            'entity' => $userEntity,
            'collection' => array(),
            'title' => 'Starred'
        );
    }
}
