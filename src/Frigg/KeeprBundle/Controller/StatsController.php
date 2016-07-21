<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Star controller.
 *
 * @Route("/stats")
 */
class StatsController extends Controller
{
    /**
     * Shows main statistics
     *
     * @Route("/", name="stats_index")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Stats:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $postTimeline = $em->getRepository('FriggKeeprBundle:Post')->loadTimelineDatapoints('json');
        $popularTags = $em->getRepository('FriggKeeprBundle:Tag')->loadPopular(10);
        $activeUsers = $em->getRepository('FriggKeeprBundle:User')->loadMostActive(5);

        return [
            'popular_tags' => $popularTags,
            'active_users' => $activeUsers
        ];
    }
}
