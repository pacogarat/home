<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Movement;
use AppBundle\Entity\Source;
use AppBundle\Entity\Tag;
use AppBundle\Form\Type\TagType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class SourceController extends Controller
{
    /**
     * @Route("/sources", name="sources")
     * @Template("AppBundle:Source:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        return [
            'menu'      => 'source'
        ];
    }

    /**
     * @Route("/sources/{slug}", name="source_global")
     * @Template("AppBundle:Source:global.html.twig")
     * @Method("GET")
     */
    public function globalAction(Source $source)
    {
        $tags = $this->getDoctrine()->getRepository('AppBundle:Tag')->findAll();
        $ingresos = $this->getDoctrine()->getRepository('AppBundle:Movement')->getIngresos($source);
        $gastos = $this->getDoctrine()->getRepository('AppBundle:Movement')->getGastos($source);
        $serializer = $this->get('jms_serializer');
        return [
            'source'            => $source,
            'source_json'       => $serializer->serialize($source,'json'),
            'tags'              => $tags,
            'ingresos'          => $ingresos,
            'gastos'            => $gastos
        ];
    }

    /**
     * @Route("/sources.json", name="sources_query")
     * @Method("GET")
     */
    public function queryAction(Request $request)
    {
        $sources = $this->getDoctrine()->getRepository('AppBundle:Source')->findAll();
        $serializer = $this->get('jms_serializer');
        $json_sources = $serializer->serialize($sources,'json');
        return new Response($json_sources);
    }

    /**
     * @Route("/sources.json/{id}", name="sources_get")
     * @Method("GET")
     */
    public function getAction(Source $source)
    {
        $serializer = $this->get('jms_serializer');
        $ingresos = $this->getDoctrine()->getRepository('AppBundle:Movement')->getIngresos($source);
        $gastos = $this->getDoctrine()->getRepository('AppBundle:Movement')->getGastos($source);
        $result = [
            'id'    => $source->getId(),
            'name'    => $source->getName(),
            'amount'    => $source->getAmount(),
            'slug'    => $source->getSlug(),
            'ingresos'  => $ingresos,
            'gastos'    => $gastos
        ];
        $result = $serializer->serialize($result,'json');
        return new Response($result);
    }

    /**
     * @Route("/sources.json", name="sources_new")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $name = $request->get('name');
        $repeated = $this->getDoctrine()->getRepository('AppBundle:Source')->findOneByName($name);
        if (!$repeated) {
            if (!empty($name)) {
                $source = new Source();
                $source->setName($name);
                $source->setAmount(0);
                $this->getDoctrine()->getManager()->persist($source);
                $this->getDoctrine()->getManager()->flush();
                return new JsonResponse([
                    'status' => true,
                    'message' => 'Origen creado'
                ]);
            }
        } else {
            return new JsonResponse([
                'status' => false,
                'message' => 'El origen ya existe'
            ]);
        }
        return new JsonResponse();
    }

    /**
     * @Route("/ajuste_saldo.json", name="ajuste_saldo")
     * @Method("POST")
     */
    public function ajusteSaldoAction(Request $request)
    {
        $source_slug = $request->get('source');
        $new_amount = $request->get('new_amount');
        $source = $this->getDoctrine()->getRepository('AppBundle:Source')->findOneBySlug($source_slug);
        if ($source) {
            $old_amount = $source->getAmount();
            $movement = new Movement();
            $movement->setAmount($new_amount - $old_amount);
            $movement->setConcept('ajuste de saldo');
            $movement->setSource($source);
            $source->setAmount($new_amount);
            $this->getDoctrine()->getManager()->persist($source);
            $this->getDoctrine()->getManager()->persist($movement);
            $this->getDoctrine()->getManager()->flush();
        }
        return new JsonResponse([
            'status' => true,
            'message' => 'Saldo de ' . $source->getName() . ' actualizado a ' . $source->getAmount() . ' €'
        ]);
    }

    /**
     * @Route("/movement.json", name="movement")
     * @Method("POST")
     */
    public function movementAction(Request $request)
    {
        $source_slug    = $request->get('source');
        $amount         = $request->get('amount');
        $tags           = $request->get('tags');
        $tags           = $this->getDoctrine()->getRepository('AppBundle:Tag')->getTagsFromArray($tags);
        $source         = $this->getDoctrine()->getRepository('AppBundle:Source')->findOneBySlug($source_slug);
        if ($source) {
            $old_amount = $source->getAmount();
            $movement = new Movement();
            $movement->setAmount($amount);
            $movement->setTags($tags);
            $movement->setConcept('movimiento');
            $movement->setSource($source);
            $source->setAmount($source->getAmount() + $amount);
            $this->getDoctrine()->getManager()->persist($source);
            $this->getDoctrine()->getManager()->persist($movement);
            $this->getDoctrine()->getManager()->flush();
        }
        return new JsonResponse([
            'status' => true,
            'message' => 'Saldo de ' . $source->getName() . ' actualizado a ' . $source->getAmount() . ' €'
        ]);
    }

}
