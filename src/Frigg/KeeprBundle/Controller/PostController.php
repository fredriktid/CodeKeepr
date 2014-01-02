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
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $collection = $em->getRepository('FriggKeeprBundle:Post')->findBy(
            array(),
            array('created_at' => 'DESC')
        );

        $limit = 20;
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $collection,
            $this->get('request')->query->get('page', 1),
            $limit
        );

        return array(
            'collection' => $pagination,
            'limit' => $limit,
            'title' => 'Home'
        );
    }

    /**
     * Main navigation menu for posts
     *
     * @Route("/navigation/{currentRoute}", name="post_navigation")
     * @Method("GET")
     * @Template()
     */
    public function navigationAction(Request $request, $currentRoute)
    {
        return array(
            'current_route' => $currentRoute
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

            // lastly persist and save the new post entity
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('post_show', array(
                'id' => $entity->getId()
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

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Post entity.
     *
     * @Route("/new", name="post_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Post();
        // voter goes here...
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl(
                'fos_user_security_login'
            ));
        }

        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{id}", name="post_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FriggKeeprBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id}/edit", name="post_edit")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:new.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FriggKeeprBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
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
            'action' => $this->generateUrl('post_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Post entity.
     *
     * @Route("/{id}", name="post_update")
     * @Method("PUT")
     * @Template("FriggKeeprBundle:Post:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('FriggKeeprBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $originalTags = new ArrayCollection();
        foreach ($entity->getTags() as $tag) {
            $originalTags->add($tag);
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

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

            return $this->redirect($this->generateUrl('post_show', array(
                'id' => $id
            )));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
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
                throw $this->createNotFoundException('Unable to find Post entity.');
            }

            foreach ($entity->getTags() as $tag) {
                $tag->removePost($entity);
                $em->persist($tag);
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
            ->setAction($this->generateUrl('post_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array(
                'label' => 'Delete'
            ))
            ->getForm()
        ;
    }
}
