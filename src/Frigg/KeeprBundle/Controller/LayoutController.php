<?php

namespace Frigg\KeeprBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Post controller.
 *
 * @Route("/layout")
 */
class LayoutController extends Controller
{
    /**
     * Sidebar template.
     *
     * @Route("/sidebar", name="layout_sidebar", defaults={"currentRoute": null})
     * @Method("GET")
     * @Template("FriggKeeprBundle:Layout:sidebar.html.twig")
     */
    public function sidebarAction($currentRoute)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $popularTags = $em->getRepository('FriggKeeprBundle:Tag')->loadPopular(10);
        $popularUsers = $em->getRepository('FriggKeeprBundle:User')->loadMostActive(5);

        return [
            'current_route' => $currentRoute,
            'tags' => $popularTags,
            'users' => $popularUsers,
        ];
    }

    /**
     * Security token.
     *
     * @Route("/token", name="layout_token")
     * @Method("GET")
     */
    public function getTokenAction()
    {
        return new Response(
            $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate')
        );
    }
}
