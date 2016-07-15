<?php

namespace Frigg\KeeprBundle\Controller;

use Frigg\KeeprBundle\Entity\Post;
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

        $em = $this->get('doctrine.orm.entity_manager');

        /** @var Post $postEntity */
        if (!$postEntity = $em->getRepository('FriggKeeprBundle:Post')->findOneById($id)) {
            $message = $translator->trans('Post not found');
            $session->getFlashBag()->add(
                'error',
                $message
            );

            throw new NotFoundHttpException(
                $message
            );
        }

        if (!$securityContext->isGranted('POST_STAR_NEW', $postEntity)) {
            $message = $translator->trans('Insufficient permissions to add star');
            $session->getFlashBag()->add(
                'error',
                $message
            );

            throw new AccessDeniedException(
                $message
            );
        }

        $securityToken = $securityContext->getToken();
        $currentUser = $securityToken->getUser();

        /** @var Star $starEntity */
        if (!$starEntity = $em->getRepository('FriggKeeprBundle:Star')->isStarred($postEntity, $currentUser)) {
            $starEntity = new Star;
            $starEntity->setUser($currentUser);
            $starEntity->setPost($postEntity);
            $em->persist($starEntity);
            $em->flush();
            $session->getFlashBag()->add(
                'success',
                $translator->trans(
                    'Added star on "topic"',
                    ['topic' => $postEntity->getTopic()]
                )
            );
        } else {
            $em->remove($starEntity);
            $em->flush();
            $session->getFlashBag()->add(
                'notice',
                $translator->trans(
                    'Unstarred "topic"',
                    ['topic' => $postEntity->getTopic()]
                )
            );
        }

        if ($referer = $this->get('request')->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirect($this->generateUrl('post_show', [
            'id' => $postEntity->getId(),
            'identifier' => $postEntity->getIdentifier()
        ]));
    }
}
