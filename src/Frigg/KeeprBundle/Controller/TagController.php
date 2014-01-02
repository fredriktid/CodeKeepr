<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('FriggKeeprBundle:Tag')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Groups and ranks all tags
     *
     * @Route("/group", name="tag_group")
     * @Method("GET")
     * @Template()
     */
    public function groupAction(Request $request)
    {
        $limit = 20;
        $currentRoute = $request->query->get('route');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $group = $qb->select('t.id, t.identifier, t.name, COUNT(p.id) AS post_count')
           ->from('FriggKeeprBundle:Post', 'p')
           ->leftJoin('p.Tags', 't')
           ->where('t.id IS NOT NULL')
           ->orderBy('post_count', 'DESC')
           ->groupBy('t.identifier')
           ->setMaxResults(20)
           ->getQuery()->getResult();

        $group = (!$group ? array() : $group);

        return array(
            'current_route' => $currentRoute,
            'group' => $group,
            'limit' => $limit
        );
    }

    /**
     * Finds and displays a Tag entity.
     *
     * @Route("/{identifier}", name="tag_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($identifier)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$tag = $em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($identifier)) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        $qb = $em->createQueryBuilder();
        $posts = $qb->select('p')
           ->from('FriggKeeprBundle:Post', 'p')
           ->leftJoin('p.Tags', 't')
           ->where('t.identifier = :identifier')
           ->orderBy('p.created_at', 'DESC')
           ->setParameter('identifier', $identifier)
           ->getQuery()->getResult();

        return array(
            'tag' => $tag,
            'posts' => $posts,
        );
    }
}
