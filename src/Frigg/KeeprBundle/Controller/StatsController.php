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
     * Add star to a post.
     *
     * @Route("/", name="stats_index")
     * @Method("GET")
     * @Template("FriggKeeprBundle:Stats:index.html.twig")
     */
    public function indexAction()
    {
        return [];
    }
}
