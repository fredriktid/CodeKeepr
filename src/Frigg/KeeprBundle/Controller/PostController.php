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
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $currentUser = $this->get('security.context')->getToken()->getUser();
        $currentUserId = (is_object($currentUser)) ? $currentUser->getId() : 0;

        $collection = $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('p.created_at', 'DESC')
            ->setParameters(array(
                'private' => 1,
                'current_user_id' => $currentUserId
            ))
            ->getQuery()->getResult();

        $pageLimit = 20;
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $collection,
            $this->get('request')->query->get('page', 1),
            $pageLimit
        );

        return array(
            'collection' => $pagination,
            'limit' => $pageLimit,
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
        $pageLimit = 20;
        $timestamp = strtotime($date);
        $collection = new ArrayCollection();

        if ($timestamp !== false) {
            $currentUser = $this->get('security.context')->getToken()->getUser();
            $currentUserId = (is_object($currentUser)) ? $currentUser->getId() : 0;

            $timestamps = array(
                'start' => mktime(0, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp)),
                'end'   => mktime(23, 59, 59, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp))
            );
            $interval = array(
                'start' => new \DateTime(date('Y-m-d H:i:s', $timestamps['start'])),
                'end'   => new \DateTime(date('Y-m-d H:i:s', $timestamps['end']))
            );

            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $collection = $qb->select('p')
                ->from('FriggKeeprBundle:Post', 'p')
                ->where($qb->expr()->between(
                    'p.created_at',
                    ':start_date',
                    ':end_date'
                ))
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->neq('p.private', ':private'),
                        $qb->expr()->andX(
                            $qb->expr()->eq('p.private', ':private'),
                            $qb->expr()->eq('p.User', ':current_user_id')
                        )
                    )
                )
                ->orderBy('p.created_at', 'DESC')
                ->setParameters(array(
                    'start_date' => $interval['start'],
                    'end_date' => $interval['end'],
                    'private' => 1,
                    'current_user_id' => $currentUserId
                ))
                ->getQuery()->getResult();

            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $collection,
                $this->get('request')->query->get('page', 1),
                $pageLimit
            );
        }

        return array(
            'collection' => $pagination,
            'limit' => $pageLimit,
            'title' => $date
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
        $pageLimit = 20;
        $collection = new ArrayCollection();
        $em = $this->getDoctrine()->getManager();

        if (!$userObject = $em->getRepository('FriggKeeprBundle:User')->find($userId)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('User not found')
            );
        }

        $currentUser = $this->get('security.context')->getToken()->getUser();
        $currentUserId = (is_object($currentUser)) ? $currentUser->getId() : 0;

        $qb = $em->createQueryBuilder();
        $collection = $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->where($qb->expr()->eq(
                'p.User',
                ':user_id'
            ))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('p.created_at', 'DESC')
            ->setParameters(array(
                'user_id' => $userObject->getId(),
                'current_user_id' => $currentUserId,
                'private' => 1,
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
            'title' => $userObject->generateUsername()
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
        // todo: a real voter goes here...
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl(
                'fos_user_security_login'
            ));
        }

        $entity = new Post();
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
        $em = $this->getDoctrine()->getManager();

        if (!$entity = $em->getRepository('FriggKeeprBundle:Post')->findOneByIdentifier($identifier)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        // todo: voter for private posts

        $deleteForm = $this->createDeleteForm($entity->getId());

        return array(
            'title'  => $entity->getTopic(),
            'entity' => $entity,
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
        $em = $this->getDoctrine()->getManager();

        if (!$entity = $em->getRepository('FriggKeeprBundle:Post')->findOneByIdentifier($identifier)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        // todo: a real voter goes here...
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl(
                'fos_user_security_login'
            ));
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($entity->getId());

        return array(
            'edit_tag' => false,
            'title'  => $this->get('translator')->trans('Edit'),
            'entity' => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
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
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('FriggKeeprBundle:Post')->findOneByIdentifier($identifier);

        if (!$entity) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find Post entity')
            );
        }

        $originalTags = new ArrayCollection();
        foreach ($entity->getTags() as $tag) {
            $originalTags->add($tag);
        }

        $deleteForm = $this->createDeleteForm($entity->getId());
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
        }

        return $this->redirect($this->generateUrl('post_show', array(
            'identifier' => $entity->getIdentifier()
        )));
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
