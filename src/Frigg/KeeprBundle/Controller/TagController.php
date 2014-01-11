<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Frigg\KeeprBundle\Entity\Tag;

/**
 * Tag controller.
 *
 * @Route("/tag")
 */
class TagController extends Controller
{
    /**
     * Lists all Tag entities.
     *
     * @Route("/", name="tag")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();
        $currentUserId = (is_object($currentUser)) ? $currentUser->getId() : 0;

        $pageLimit = 100;
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $collection = $qb->select('t.id, t.identifier, t.name, COUNT(p.id) AS post_count')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Tags', 't')
            ->where('t.id IS NOT NULL')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('t.name', 'ASC')
            ->groupBy('t.identifier')
            ->setParameters(array(
                'private' => 1,
                'current_user_id' => $currentUserId
            ))
            ->setMaxResults($pageLimit)
            ->getQuery()->getResult();

        $share = array();
        foreach ($collection as $item) {
            $share[$item['identifier']] = $item['post_count'];
        }

        $total = array_sum($share);
        $share = array_map(function($hits) use ($total) {
           return round($hits / $total * 100, 1);
        }, $share);

        return array(
            'title' => $this->get('translator')->trans('Tags'),
            'collection' => $collection,
            'collection_share' => $share
        );
    }

    /**
     * Groups and ranks all tags
     *
     * @Route("/group/{currentIdentifier}", name="tag_group", defaults={"currentIdentifier" = null})
     * @Method("GET")
     * @Template()
     */
    public function groupAction(Request $request, $currentIdentifier = null)
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();
        $currentUserId = (is_object($currentUser)) ? $currentUser->getId() : 0;

        $pageLimit = 15;
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $group = $qb->select('t.id, t.identifier, t.name, COUNT(p.id) AS post_count')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Tags', 't')
            ->where('t.id IS NOT NULL')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('post_count', 'DESC')
            ->groupBy('t.identifier')
            ->setMaxResults($pageLimit)
            ->setParameters(array(
                'private' => 1,
                'current_user_id' => $currentUserId
            ))
            ->getQuery()->getResult();

        $group = (!$group ? new ArrayCollection() : $group);

        return array(
            'current_identifier' => $currentIdentifier,
            'group' => $group,
            'limit' => $pageLimit
        );
    }

    /**
     * Finds and displays a Tag entity.
     *
     * @Route("/{identifier}", name="tag_show")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Post:paginator.html.twig")
     */
    public function showAction($identifier)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$entity = $em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($identifier)) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('Unable to find tag')
            );
        }

        $currentUser = $this->get('security.context')->getToken()->getUser();
        $currentUserId = (is_object($currentUser)) ? $currentUser->getId() : 0;

        $pageLimit = 20;
        $qb = $em->createQueryBuilder();
        $collection = $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Tags', 't')
            ->where('t.identifier = :identifier')
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
                'identifier' => $identifier,
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

        return array(
            'tag_identifier' => $entity->getIdentifier(),
            'collection' => $pagination,
            'limit' => $pageLimit,
            'title' => $entity->getName()
        );
    }

    /**
     * Search for tag, returns json
     *
     * @Route("/search/json", name="tag_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction()
    {
        $query = $this->get('request')->query->get('query');
        $method = $this->get('request')->query->get('method');

        $collection = array();
        if (strlen($query) > 0) {
            $pageLimit = 20;
            $finder = $this->get('fos_elastica.finder.website.tag');
            $tags = $finder->find($query.'*', 10);
            foreach($tags as $tag) {
                $collection[] = array(
                    'label' => $tag->getName(),
                    'value' => $tag->getName()
                );
            }
        }

        switch ($method) {
            case 'json':
                $response = new Response(json_encode($collection));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            default:
                echo 'Method not supported'; die;
        }
    }
}
