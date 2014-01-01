<?php

namespace Frigg\KeeprBundle\Controller;


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
    public function groupAction()
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $group = $qb->select('t.id, t.name, COUNT(p.id) AS post_count')
           ->from('FriggKeeprBundle:Post', 'p')
           ->leftJoin('p.Tags', 't')
           ->where('t.id IS NOT NULL')
           ->orderBy('post_count', 'DESC')
           ->groupBy('t.id')
           ->getQuery()->getResult();

        $group = (!$group ? array() : $group);

        return array(
            'group' => $group,
        );
    }


    /**
     * Finds and displays a Tag entity.
     *
     * @Route("/{id}", name="tag_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $tag = $em->getRepository('FriggKeeprBundle:Tag')->find($id);

        if (!$tag) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        return array(
            'tag' => $tag,
        );
    }
}
