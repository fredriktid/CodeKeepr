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
     * @Route("/{id}", requirements={"id" = "\d+"},  name="star_switch")
     * @Method("GET")
     */
    public function switchAction($id)
    {
        $session = $this->get('session');
        $translator = $this->get('translator');
        $securityContext = $this->get('security.context');

        if (!$securityContext->isGranted('ROLE_USER')) {
            $message = $translator->trans('You must be logged in to star something');
            $session->getFlashBag()->add(
                'error',
                $message
            );

            throw new AccessDeniedException(
                $message
            );
        }

        $postService = $this->get('codekeepr.post.service');

        if (!$post = $postService->loadById($id)) {
            $message = $translator->trans('Post not found');
            $session->getFlashBag()->add(
                'error',
                $message
            );

            throw new NotFoundHttpException(
                $message
            );
        }

        if (!$securityContext->isGranted('POST_STAR_NEW', $post)) {
            $message = $translator->trans('Insufficient permissions to add star');
            throw new AccessDeniedException(
                $message
            );
        }

        $em = $this->getDoctrine()->getManager();
        if (!$star = $postService->isStarred($post)) {
            $currentUser = $securityContext->getToken()->getUser();
            $star = new Star;
            $star->setUser($currentUser);
            $star->setPost($post);
            $em->persist($star);
            $em->flush();
            $session->getFlashBag()->add(
                'success',
                $translator->trans(
                    'Added star on "topic"',
                    ['topic' => $post->getTopic()]
                )
            );
        } else {
            $em->remove($star);
            $em->flush();
            $session->getFlashBag()->add(
                'notice',
                $translator->trans(
                    'Unstarred "topic"',
                    ['topic' => $post->getTopic()]
                )
            );
        }

        if ($referer = $this->get('request')->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirect($this->generateUrl('post_show', [
            'id' => $post->getId(),
            'identifier' => $post->getIdentifier()
        ]));
    }
}
