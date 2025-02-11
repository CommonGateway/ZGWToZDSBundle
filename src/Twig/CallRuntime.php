<?php

namespace CommonGateway\ZGWToZDSBundle\Twig;

use Adbar\Dot;
use CommonGateway\CoreBundle\Service\CallService;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class CallRuntime implements RuntimeExtensionInterface
{

    public function __construct(private readonly CallService $callService)
    {
    }

    /**
     * Call source of given id or reference 
     *
     * @param array $array The array to turn into a dot array.
     *
     * @return array The dot aray.
     */
    public function call(string $sourceId, string $endpoint, string $method = 'GET', array $configuration = []): array
    {
        return [];
    }
}
