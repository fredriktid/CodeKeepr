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
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('You must be logged in to star something')
            );

            return $this->redirect($referer);
        }

        $session = $this->get('session');
        $translator = $this->get('translator');
        $postService = $this->get('codekeepr.post.service');

        if (!$post = $postService->loadById($id)) {
            throw new NotFoundHttpException(
                $translator->trans('Post not found')
            );
        }

        if (!$star = $postService->isStarred($post)) {
            if (!$this->get('security.context')->isGranted('POST_STAR_NEW', $post)) {
                throw new AccessDeniedException;
            }

            $currentUser = $this->get('security.context')->getToken()->getUser();

            $star = new Star;
            $star->setUser($currentUser);
            $star->setPost($post);

            $em = $this->getDoctrine()->getManager();
            $em->persist($star);
            $em->flush();

            $session->getFlashBag()->add(
                'success',
                $translator->trans(
                    'Added star on "topic"',
                    ['topic' => $post->getTopic()]
                )
            );
        }

        $referer = $this->get('request')->headers->get('referer');
        $referer = ($referer ?: $this->generateUrl('post'));

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
        $postService = $this->get('codekeepr.post.service');

        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('You must be logged in to star something')
            );

            return $this->redirect($referer);
        }

        if (!$post = $postService->loadById($id)) {
            throw new NotFoundHttpException(
                $translator->trans('Post not found"')
            );
        }

        if (!$star = $postService->isStarred($post)) {
            throw new NotFoundHttpException(
                $translator->trans('Star not found')
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($star);
        $em->flush();

        $session->getFlashBag()->add(
            'notice',
            $translator->trans(
                'Unstarred "topic"',
                ['topic' => $post->getTopic()]
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