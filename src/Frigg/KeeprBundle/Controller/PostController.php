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
use Frigg\KeeprBundle\Form\PostType;

/**
 * Post controller.
 *
 * @Route("/post")
 */
class PostController extends Controller
{
    /**
     * Lists all Post entities.
     *
     * @Route("/", name="post")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function indexAction()
    {
        $userService = $this->get('codekeepr.service.user');
        $postService = $this->get('codekeepr.service.post');
        $postService->setUserService($userService);
        $postService->loadAll();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $postService->getCollection(),
            $this->get('request')->query->get('page', 1),
            $postService->getConfig('page_limit')
        );

        return array(
            'collection' => $pagination,
            'limit' => $postService->getConfig('page_limit'),
            'title' => $this->get('translator')->trans('Home')
        );
    }

    /**
     * Posts by date
     *
     * @Route("/date/{date}", name="post_date")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function dateAction($date)
    {
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            $userService = $this->get('codekeepr.service.user');
            $postService = $this->get('codekeepr.service.post');
            $postService->setUserService($userService);
            $postService->loadByDay($timestamp);

            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $postService->getCollection(),
                $this->get('request')->query->get('page', 1),
                $postService->getConfig('page_limit')
            );
        }

        return array(
            'collection' => $pagination,
            'limit' => $postService->getConfig('page_limit'),
            'title' => $this->get('translator')->trans('By date')
        );
    }

    /**
     * Posts by date
     *
     * @Route("/user/{userId}", requirements={"userId" = "\d+"}, name="post_user")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function userAction($userId)
    {
        $userService = $this->get('codekeepr.service.user');
        $userService->loadEntityById($userId);
        if (!$userService->getEntity()) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('User not found')
            );
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
            'limit' => $postService->getConfig('page_limit'),
            'title' => $postService->getUserService()->generateUsername()
        );
    }


    /**
     * Creates a new Post entity.
     *
     * @Route("/", name="post_create")
     * @Method("POST")
     * @Template("FriggKeeprBundle:Post:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Post();

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // set user
            $currentUser = $this->get('security.context')->getToken()->getUser();
            $entity->setUser($currentUser);

            // process tags
            foreach ($entity->getTags() as $tag) {
                // if the tag already exists we need to remove the new one from the form collection
                // and then associate the existing tag with this post instead
                if ($currentTag = $em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($tag->getIdentifier())) {
                    $entity->getTags()->removeElement($tag);
                    $entity->addTag($currentTag);
                    $currentTag->addPost($entity);
                    $em->persist($currentTag);
                    continue;
                }

                // but if it doest exist, associate and persist new tag
                $tag->addPost($entity);
                $em->persist($tag);
            }

            // lastly persist and save the new post entity
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('post_show', array(
                'identifier' => $entity->getIdentifier()
            )));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
    * Creates a form to create a Post entity.
    *
    * @param Post $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Post $entity)
    {
        $form = $this->createForm(new PostType(), $entity, array(
            'action' => $this->generateUrl('post_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array(
            'label' => $this->get('translator')->trans('Create')
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Post entity.
     *
     * @Route("/new", name="post_new")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:new.html.twig")
     */
    public function newAction()
    {
        $entity = new Post();
        if (!$this->get('security.context')->isGranted('POST_NEW', $entity)) {
            throw new AccessDeniedException;
        }

        $form = $this->createCreateForm($entity);

        return array(
            'edit_tag' => true,
            'title'  => $this->get('translator')->trans('Add code'),
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{identifier}", name="post_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($identifier)
    {
        $postService = $this->get('codekeepr.service.post');
        $postService->loadEntityByIdentifier($identifier);

        if (!$postEntity = $postService->getEntity()) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        if (!$this->get('security.context')->isGranted('POST_SHOW', $postEntity)) {
            throw new AccessDeniedException;
        }

        $deleteForm = $this->createDeleteForm($postEntity->getId());

        return array(
            'title'  => $postEntity->getTopic(),
            'entity' => $postEntity,
            'delete_form' => $deleteForm->createView()
        );
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{identifier}/edit", name="post_edit")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:new.html.twig")
     */
    public function editAction($identifier)
    {
        $postService = $this->get('codekeepr.service.post');
        $postService->loadEntityByIdentifier($identifier);

        if (!$postEntity = $postService->getEntity()) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        if (!$this->get('security.context')->isGranted('POST_EDIT', $postEntity)) {
            throw new AccessDeniedException;
        }

        $editForm = $this->createEditForm($postEntity);

        return array(
            'edit_tag' => false,
            'entity' => $postEntity,
            'form'   => $editForm->createView(),
            'title'  => $this->get('translator')->trans(
                'Edit "topic"',
                array('topic' => $postEntity->getTopic())
            )
        );
    }

    /**
    * Creates a form to edit a Post entity.
    *
    * @param Post $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Post $entity)
    {
        $form = $this->createForm(new PostType(), $entity, array(
            'action' => $this->generateUrl('post_update', array(
                'identifier' => $entity->getIdentifier()
            )),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array(
            'label' => $this->get('translator')->trans('Update')
        ));

        return $form;
    }
    /**
     * Edits an existing Post entity.
     *
     * @Route("/{identifier}", name="post_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $identifier)
    {
        $postService = $this->get('codekeepr.service.post');
        $postService->loadEntityByIdentifier($identifier);

        if (!$entity = $postService->getEntity()) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        $originalTags = new ArrayCollection();
        foreach ($entity->getTags() as $tag) {
            $originalTags->add($tag);
        }

        $deleteForm = $this->createDeleteForm($entity->getId());
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        $em = $postService->getEntityManager();
        if ($editForm->isValid()) {
            // remove tags
            foreach ($originalTags as $tag) {
                if (false === $entity->getTags()->contains($tag)) {
                    $tag->removePost($entity);
                    $em->persist($tag);
                }
            }

            // add tags
            foreach ($entity->getTags() as $tag) {
                // if the tag already exists we need to remove the new one from the form collection
                // and then associate the existing tag with the this post instead
                if ($currentTag = $em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($tag->getIdentifier())) {
                    $entity->getTags()->removeElement($tag);
                    $entity->addTag($currentTag);
                    $currentTag->addPost($entity);
                    $em->persist($currentTag);
                    continue;
                }

                // but if it doest exist, associate and persist new tag
                $tag->addPost($entity);
                $em->persist($tag);
            }

            $em->persist($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('post_show', array(
            'identifier' => $entity->getIdentifier()
        )));
    }

    /**
     * Confirms deletion of an entity
     *
     * @Route("/{identifier}/delete", name="post_delete_confirm")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:delete.html.twig")
     */
    public function deleteConfirmAction($identifier)
    {
        $postService = $this->get('codekeepr.service.post');
        $postService->loadEntityByIdentifier($identifier);

        if (!$entity = $postService->getEntity()) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        if (!$this->get('security.context')->isGranted('POST_DELETE', $entity)) {
            throw new AccessDeniedException;
        }

        $deleteForm = $this->createDeleteForm($entity->getId());

        return array(
            'title' => $this->get('translator')->trans(
                'Confirm delete of "topic"',
                array('topic' => $entity->getTopic())
            ),
            'delete_form' => $deleteForm->createView()
        );
    }


    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}", name="post_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FriggKeeprBundle:Post')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException(
                    $this->get('translator')->trans('Unable to find Post entity')
                );
            }

            foreach ($entity->getTags() as $tag) {
                $tag->removePost($entity);
                $em->persist($tag);
            }

            foreach ($entity->getStars() as $star) {
                $em->remove($star);
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('post'));
    }

    /**
     * Creates a form to delete a Post entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', array(
                'id' => $id
            )))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array(
                'label' => $this->get('translator')->trans('Delete')
            ))
            ->getForm()
        ;
    }
}
