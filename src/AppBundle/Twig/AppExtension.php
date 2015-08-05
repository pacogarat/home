<?php

namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('total', array($this, 'totalFilter')),
        );
    }

    public function totalFilter($array_collection)
    {
        $result = 0;
        foreach ($array_collection as $movement) {
            $result += $movement->getAmount();
        }

        return $result;
    }

    public function getName()
    {
        return 'app_extension';
    }
}