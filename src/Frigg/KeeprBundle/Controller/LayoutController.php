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
