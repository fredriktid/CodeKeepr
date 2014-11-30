<?php

namespace Frigg\KeeprBundle\Controller;

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
     * All posts
     *
     * @Route("/", name="post")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function indexAction()
    {
        $postService = $this->get('codekeepr.post.service');
        $posts = $postService->load();
        $limit = $postService->getConfig('page_limit');
        $page = $this->get('request')->query->get('page', 1);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $posts,
            $page,
            $limit
        );

        return [
            'posts' => $pagination,
            'title' => $this->get('translator')->trans('Home')
        ];
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
        $postService = $this->get('codekeepr.post.service');
        $posts = $postService->loadPeriod(
            mktime(0, 0, 0, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp)),
            mktime(23, 59, 59, date('n', $timestamp), date('j', $timestamp), date('Y', $timestamp))
        );

        $limit = $postService->getConfig('page_limit');
        $page = $this->get('request')->query->get('page', 1);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $posts,
            $page,
            $limit
        );

        return [
            'posts' => $pagination,
            'title' => $this->get('translator')->trans('By date')
        ];
    }

    /**
     * Posts by date
     *
     * @Route("/user/{id}", requirements={"id" = "\d+"}, name="post_user")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function userAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$user = $em->getRepository('FriggKeeprBundle:User')->find($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('User not found')
            );
        }

        $postService = $this->get('codekeepr.post.service');
        $posts = $postService->loadByUser($user);
        $limit = $postService->getConfig('page_limit');
        $page = $this->get('request')->query->get('page', 1);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $posts,
            $page,
            $limit
        );

        return [
            'posts' => $pagination,
            'title' => $user->getUsername()
        ];
    }

    /**
     * Starred post by user
     *
     * @Route("/user/{id}/starred", name="post_user_star")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function starredAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$user = $em->getRepository('FriggKeeprBundle:User')->find($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('User not found')
            );
        }

        if (!$this->get('security.context')->isGranted('USER_STAR_SHOW', $user)) {
            throw new AccessDeniedException;
        }

        $postService = $this->get('codekeepr.post.service');
        $posts = $postService->loadStarred($user);
        $limit = $postService->getConfig('page_limit');
        $page = $this->get('request')->query->get('page', 1);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $posts,
            $page,
            $limit
        );

        return [
            'posts' => $pagination,
            'title' => $this->get('translator')->trans('Starred')
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
        $postService = $this->get('codekeepr.post.service');
        if (!$post = $postService->loadById($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        if (!$this->get('security.context')->isGranted('POST_SHOW', $post)) {
            throw new AccessDeniedException;
        }

        $deleteForm = $this->createDeleteForm($post->getId());

        return [
            'title'  => $post->getTopic(),
            'entity' => $post,
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
        $postService = $this->get('codekeepr.post.service');
        if (!$post = $postService->loadById($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        if (!$this->get('security.context')->isGranted('POST_EDIT', $post) && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException;
        }

        $editForm = $this->createEditForm($post);

        return [
            'edit_tag' => false,
            'entity' => $post,
            'form'   => $editForm->createView(),
            'title'  => $this->get('translator')->trans(
                'Edit "topic"',
                ['topic' => $post->getTopic()]
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
        $postService = $this->get('codekeepr.post.service');
        if (!$post = $postService->loadById($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        $originalTags = new ArrayCollection();
        foreach ($post->getTags() as $tag) {
            $originalTags->add($tag);
        }

        $deleteForm = $this->createDeleteForm($post->getId());
        $editForm = $this->createEditForm($post);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        if ($editForm->isValid()) {
            // remove tags
            foreach ($originalTags as $tag) {
                if (false === $post->getTags()->contains($tag)) {
                    $tag->removePost($post);
                    $em->persist($tag);
                }
            }

            // add tags
            foreach ($post->getTags() as $tag) {
                // if the tag already exists we need to remove the new one from the form collection
                // and then associate the existing tag with the this post instead
                if ($currentTag = $em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($tag->getIdentifier())) {
                    $post->getTags()->removeElement($tag);
                    $post->addTag($currentTag);
                    $currentTag->addPost($post);
                    $em->persist($currentTag);
                    continue;
                }

                // but if it doest exist, associate and persist new tag
                $tag->addPost($post);
                $em->persist($tag);
            }

            $em->persist($post);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('post_show', [
            'id' => $post->getId(),
            'identifier' => $post->getIdentifier()
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
        $postService = $this->get('codekeepr.post.service');
        if (!$post = $postService->loadById($id)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find post')
            );
        }

        if (!$this->get('security.context')->isGranted('POST_DELETE', $post)) {
            throw new AccessDeniedException;
        }

        $deleteForm = $this->createDeleteForm($post->getId());

        return [
            'title' => $this->get('translator')->trans(
                'Confirm delete of "topic"',
                ['topic' => $post->getTopic()]
            ),
            'delete_form' => $deleteForm->createView()
        ];
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

            if (!$this->get('security.context')->isGranted('POST_DELETE', $entity)) {
                throw new AccessDeniedException;
            }

            foreach ($entity->getTags() as $tag) {
                $tag->removePost($entity);
                $em->persist($tag); 
            }

            foreach ($entity->getStars() as $star) {
                $em->remove($star);
                $em->persist($star);
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
            ->setMethod('DELETE')
            ->add('submit', 'submit', [
                'label' => $this->get('translator')->trans('Delete')
            ])
            ->getForm()
        ;
    }
}
