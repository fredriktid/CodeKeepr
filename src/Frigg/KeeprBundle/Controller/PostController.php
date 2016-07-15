<?php

namespace Frigg\KeeprBundle\Controller;

use Frigg\KeeprBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Frigg\KeeprBundle\Entity\Post;
use Frigg\KeeprBundle\Form\PostType;

/**
 * Post controller.
 *
 * @Route("/post")
 */
class PostController extends Controller
{
    /**
     * Loads all public posts
     *
     * @Route("/", name="post")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $publicPosts = $em->getRepository('FriggKeeprBundle:Post')->loadPublic();
        $currentPage = $this->get('request')->query->get('page', 1);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $publicPosts,
            $currentPage,
            20
        );

        return [
            'posts' => $pagination,
            'title' => $this->get('translator')->trans('Home')
        ];
    }

    /**
     * Public posts by date
     *
     * @Route("/date/{date}", name="post_date")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function dateAction($date)
    {
        $timestamp = strtotime($date);
        $interval = [
            'begin' => mktime(0, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp)),
            'end' => mktime(23, 59, 59, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp))
        ];

        $em = $this->get('doctrine.orm.entity_manager');
        $publicPosts = $em->getRepository('FriggKeeprBundle:Post')->loadPeriod($interval['begin'], $interval['end']);
        $currentPage = $this->get('request')->query->get('page', 1);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $publicPosts,
            $currentPage,
            20
        );

        return [
            'posts' => $pagination,
            'title' => $this->get('translator')->trans('By date')
        ];
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
            $em = $this->get('doctrine.orm.entity_manager');

            // set user
            $currentUser = $this->get('security.context')->getToken()->getUser();
            $entity->setUser($currentUser);

            // if the tag already exists we need to remove the new one from the form collection
            // and then associate the existing tag with this post instead
            foreach ($entity->getTags() as $tag) {
                /** @var Tag $currentTag */
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

            return $this->redirect(
                $this->generateUrl('post_show', [
                    'id' => $entity->getId(),
                    'identifier' => $entity->getIdentifier()
                ])
            );
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView()
        ];
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
        $form = $this->createForm(new PostType(), $entity, [
            'action' => $this->generateUrl('post_create'),
            'method' => 'POST',
        ]);

        $form->add('submit', 'submit', [
            'label' => $this->get('translator')->trans('Create')
        ]);

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

        return [
            'edit_tag' => true,
            'title'  => $this->get('translator')->trans('Add code'),
            'entity' => $entity,
            'form'   => $form->createView()
        ];
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{id}/{identifier}", name="post_show", requirements={"id" = "\d+"}, defaults={"identifier" = null})
     * @Method("GET")
     * @Template()
     */
    public function showAction($id, $identifier = null)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var Post $postEntity */
        if (!$postEntity = $em->getRepository('FriggKeeprBundle:Post')->findOneById($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        if (!$this->get('security.context')->isGranted('POST_SHOW', $postEntity)) {
            throw new AccessDeniedException;
        }

        $deleteForm = $this->createDeleteForm($postEntity->getId());

        return [
            'title'  => $postEntity->getTopic(),
            'entity' => $postEntity,
            'delete_form' => $deleteForm->createView()
        ];
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/edit/{id}", name="post_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:new.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var Post $postEntity */
        if (!$postEntity = $em->getRepository('FriggKeeprBundle:Post')->findOneById($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        if (!$this->get('security.context')->isGranted('POST_EDIT', $postEntity)) {
            $message = $this->get('translator')->trans('Access denied');
            $this->get('session')->getFlashBag()->add(
                'error',
                $message
            );

            throw new AccessDeniedException;
        }

        $editForm = $this->createEditForm($postEntity);

        return [
            'edit_tag' => false,
            'entity' => $postEntity,
            'form'   => $editForm->createView(),
            'title'  => $this->get('translator')->trans(
                'Edit "topic"',
                ['topic' => $postEntity->getTopic()]
            )
        ];
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
        $form = $this->createForm(new PostType(), $entity, [
            'action' => $this->generateUrl('post_update', [
                'id' => $entity->getId()
            ]),
            'method' => 'POST',
        ]);

        $form->add('submit', 'submit', [
            'label' => $this->get('translator')->trans('Update')
        ]);

        return $form;
    }

    /**
     * Edits an existing Post entity.
     *
     * @Route("/{id}", name="post_update")
     * @Method("POST")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var Post $postEntity */
        if (!$postEntity = $em->getRepository('FriggKeeprBundle:Post')->findOneById($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        $originalTags = new ArrayCollection();
        foreach ($postEntity->getTags() as $tag) {
            $originalTags->add($tag);
        }

        $this->createDeleteForm($postEntity->getId());
        $editForm = $this->createEditForm($postEntity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            // remove tags
            foreach ($originalTags as $tag) {
                if (false === $postEntity->getTags()->contains($tag)) {
                    $tag->removePost($postEntity);
                    $em->persist($tag);
                }
            }

            // if the tag already exists we need to remove the new one from the form collection
            // and then associate the existing tag with the this post instead
            foreach ($postEntity->getTags() as $tag) {
                /** @var Tag $currentTag */
                if ($currentTag = $em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($tag->getIdentifier())) {
                    $postEntity->getTags()->removeElement($tag);
                    $postEntity->addTag($currentTag);
                    $currentTag->addPost($postEntity);
                    $em->persist($currentTag);
                    continue;
                }

                // but if it doest exist, associate and persist new tag
                $tag->addPost($postEntity);
                $em->persist($tag);
            }

            $em->persist($postEntity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('post_show', [
            'id' => $postEntity->getId(),
            'identifier' => $postEntity->getIdentifier()
        ]));
    }

    /**
     * Confirms deletion of an entity
     *
     * @Route("/delete/{id}", name="post_delete_confirm")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:delete.html.twig")
     */
    public function deleteConfirmAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var Post $postEntity */
        if (!$postEntity = $em->getRepository('FriggKeeprBundle:Post')->findOneById($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        if (!$this->get('security.context')->isGranted('POST_DELETE', $postEntity)) {
            throw new AccessDeniedException;
        }

        $deleteForm = $this->createDeleteForm($postEntity->getId());

        return [
            'title' => $this->get('translator')->trans(
                'Confirm delete of "topic"',
                [
                    'topic' => $postEntity->getTopic()
                ]
            ),
            'delete_form' => $deleteForm->createView()
        ];
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/delete/{id}", name="post_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $entity = $em->getRepository('FriggKeeprBundle:Post')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException(
                    $this->get('translator')->trans('Unable to find Post entity')
                );
            }

            if (!$this->get('security.context')->isGranted('POST_DELETE', $entity)) {
                $message = $this->get('translator')->trans('Access denied');
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $message
                );

                throw new AccessDeniedException;
            }

            foreach ($entity->getTags() as $tag) {
                $tag->removePost($entity);
                $em->persist($tag);
            }

            foreach ($entity->getStars() as $star) {
                $em->remove($star);
                $em->flush();
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
            ->setAction($this->generateUrl('post_delete', [
                'id' => $id
            ]))
            ->setMethod('POST')
            ->add('submit', 'submit', [
                'label' => $this->get('translator')->trans('Delete')
            ])
            ->getForm()
        ;
    }

    /**
     * Handles threads and comments
     *
     * @Route("/thread/{threadId}", name="post_thread")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:thread.html.twig")
     */
    public function threadAction(Request $request, $threadId)
    {
        $thread = $this->container->get('fos_comment.manager.thread')->findThreadById($threadId);

        if (null === $thread) {
            $thread = $this->container->get('fos_comment.manager.thread')->createThread();
            $thread->setId($threadId);
            $thread->setPermalink($request->getUri());
            $this->container->get('fos_comment.manager.thread')->saveThread($thread);
        }

        $comments = $this->container->get('fos_comment.manager.comment')->findCommentTreeByThread($thread);

        return [
            'comments' => $comments,
            'thread' => $thread
        ];
    }
}
