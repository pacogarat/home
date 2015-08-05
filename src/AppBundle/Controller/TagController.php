<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tag;
use AppBundle\Form\Type\TagType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    /**
     * @Route("/tags", name="tags")
     * @Template("AppBundle:Tag:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $tag = new Tag();
        $form = $this->createForm(new TagType(), $tag);
        return [
            'menu'      => 'tag',
            'form'      => $form->createView()
        ];
    }

    /**
     * @Route("/tags.json", name="tags_query")
     * @Method("GET")
     */
    public function queryAction(Request $request)
    {
        $tags = $this->getDoctrine()->getRepository('AppBundle:Tag')->findAll();
        $serializer = $this->get('jms_serializer');
        $tags_json = $serializer->serialize($tags,'json');
        return new Response($tags_json);
    }

    /**
     * @Route("/tags.json", name="tags_new")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $name = $request->get('name');
        $repeated = $this->getDoctrine()->getRepository('AppBundle:Tag')->findOneByName($name);
        if (!$repeated) {
            if (!empty($name)) {
                $tag = new Tag();
                $tag->setName($name);
                $this->getDoctrine()->getManager()->persist($tag);
                $this->getDoctrine()->getManager()->flush();
                return new JsonResponse([
                    'status' => true,
                    'message' => 'Etiqueta creada'
                ]);
            }
        } else {
            return new JsonResponse([
                'status' => false,
                'message' => 'La etiqueta ya existe'
            ]);
        }
        return new JsonResponse();
    }

}
