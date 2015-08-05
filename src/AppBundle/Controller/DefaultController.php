<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage_global")
     * @Template("AppBundle:Default:home.html.twig")
     */
    public function indexAction(Request $request)
    {
        return [
            'menu' => 'homepage'
        ];
    }

    /**
     * @Route("/menu", name="homepage_menu")
     * @Template("AppBundle:Blocks:menu.html.twig")
     */
    public function menuAction(Request $request)
    {
        $stack = $this->get('request_stack');
        $masterRequest = $stack->getMasterRequest();
        $currentRoute = $masterRequest->get('_route');
        $sources = $this->getDoctrine()->getRepository('AppBundle:Source')->findAll();
        return [
            'sources' => $sources,
            'currentRequest' => $masterRequest->attributes->all()
        ];
    }
}
