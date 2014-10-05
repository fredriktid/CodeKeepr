<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Frigg\KeeprBundle\Entity\Star;

/**
 * Star controller.
 *
 * @Route("/star")
 */
class StarController extends Controller
{
    /**
     * Add star to a post
     *
     * @Route("/add/{id}", requirements={"id" = "\d+"},  name="post_star_add")
     * @Method("GET")
     */
    public function addAction($id)
    {
        $session = $this->get('session');
        $translator = $this->get('translator');

        $referer = $this->get('request')->headers->get('referer');
        $referer = (!$referer ?: $this->generateUrl('post'));

        $securityContext = $this->get('security.context');
        if (!$securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException;
        }

        $em = $this->getDoctrine()->getManager();
        if (!$post = $em->getRepository('FriggKeeprBundle:Post')->find($id)) {
            throw new NotFoundHttpException(
                $translator->trans('Post not found')
            );
        }

        $user = $securityContext->getToken()->getUser();
        $star = $em->getRepository('FriggKeeprBundle:Star')->findOneBy(array(
            'User' => $user->getId(),
            'Post' => $id
        ));

        if (!$star) {
            if (!$securityContext->isGranted('POST_STAR_NEW', $post)) {
                throw new AccessDeniedException;
            }

            $star = new Star;
            $star->setUser($user);
            $star->setPost($post);
            $em->persist($star);
            $em->flush();
            $session->getFlashBag()->add(
                'success',
                $translator->trans(
                    'Added star on "topic"',
                    array(
                        'topic' => $post->getTopic()
                    )
                )
            );
        }

        return $this->redirect($referer);
    }

    /**
     * Remove star from post
     *
     * @Route("/delete/{id}", requirements={"id" = "\d+"},  name="post_star_delete")
     * @Method("GET")
     */
    public function deleteAction($id)
    {
        $session = $this->get('session');
        $translator = $this->get('translator');
        $securityContext = $this->get('security.context');
        $em = $this->getDoctrine()->getManager();

        $session = $this->get('session');
        $translator = $this->get('translator');

        if (!$securityContext->isGranted('ROLE_USER')) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('You must be logged in to star something')
            );

            return $this->redirect($referer);
        }

        if (!$post = $em->getRepository('FriggKeeprBundle:Post')->find($id)) {
            throw new NotFoundHttpException(
                $translator->trans('Post not found"')
            );
        }

        $user = $securityContext->getToken()->getUser();
        $starEntity = $em->getRepository('FriggKeeprBundle:Star')->findOneBy(array(
            'User' => $user->getId(),
            'Post' => $id
        ));

        if (!$starEntity) {
            throw $this->createNotFoundException(
                $translator->trans('Star not found')
            );
        }

        if (!$securityContext->isGranted('POST_STAR_REMOVE', $starEntity)) {
            throw new AccessDeniedException;
        }

        $em->remove($starEntity);
        $em->flush();
        $session->getFlashBag()->add(
            'notice',
            $translator->trans(
                'Unstarred "topic"',
                array('topic' => $post->getTopic())
            )
        );

        if ($referer = $this->get('request')->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirect($this->generateUrl(
            'post'
        ));
    }
}