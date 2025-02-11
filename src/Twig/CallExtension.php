<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CallExtension extends AbstractExtension
{


    public function getFunctions()
    {
        return [
            new TwigFunction('map', [CallRuntime::class, 'map']),
            new TwigFunction('dotToObject', [CallRuntime::class, 'dotToArray']),
            new TwigFunction('arrayValues', [CallRuntime::class, 'arrayValues']),
            new TwigFunction('getObject', [CallRuntime::class, 'getObject']),
        ];

    }//end getFunctions()


}//end class
