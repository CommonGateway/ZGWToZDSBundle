<?php

namespace CommonGateway\ZGWToZDSBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CallExtension extends AbstractExtension
{


    public function getFilters(): array
    {
        return [
            new TwigFilter('b64enc', [CallRuntime::class, 'b64enc']),
            new TwigFilter('b64dec', [CallRuntime::class, 'b64dec']),
        ];

    }//end getFilters()


    public function getFunctions(): array
    {
        return [
            new TwigFunction('call', [CallRuntime::class, 'call']),
        ];

    }//end getFunctions()


}//end class
