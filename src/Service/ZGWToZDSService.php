<?php
/**
 * An example service for adding business logic to your class.
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace CommonGateway\ZGWToZDSBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ZGWToZDSService
{

    /**
     * @var array
     */
    private array $configuration;

    /**
     * @var array
     */
    private array $data;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * The plugin logger.
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * The mapping service.
     *
     * @var MappingService  $mappingService
     */
    private MappingService $mappingService;

    /**
     * The Resource service.
     *
     * @var GatewayResourceService $resourceService
     */
    private GatewayResourceService $resourceService;

    /**
     * The call service.
     *
     * @var CallService $callService
     */
    private CallService $callService;


    /**
     * @param EntityManagerInterface $entityManager The Entity Manager.
     * @param LoggerInterface        $pluginLogger  The plugin version of the logger interface.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $pluginLogger,
        MappingService $mappingService,
        GatewayResourceService $resourceService,
        CallService $callService
    ) {
        $this->entityManager   = $entityManager;
        $this->logger          = $pluginLogger;
        $this->configuration   = [];
        $this->data            = [];
        $this->mappingService  = $mappingService;
        $this->resourceService = $resourceService;

    }//end __construct()


    /**
     * An example handler that is triggered by an action.
     *
     * @param array $data          The data array
     * @param array $configuration The configuration array
     *
     * @return array A handler must ALWAYS return an array
     */
    public function zgwToZdsHandler(array $data, array $configuration): array
    {
        $this->data          = $data;
        $this->configuration = $configuration;

        $this->logger->debug("ZGWToZDSService -> ZGWToZDSHandler()");

        return ['response' => 'Hello. Your ZGWToZDSBundle works'];

    }//end zgwToZdsHandler()

    public function zgwToZdsDi02Handler(array $data, array $configuration): array
    {
        $toMapping   = $this->resourceService->getMapping('https://zds.nl/mapping/zds.zgwZaakToDi02.mapping.json');
        $fromMapping = $this->resourceService->getMapping('https://zds.nl/mapping/zds.Du02ToZgwZaak.mapping.json');
        $source      = $this->resourceService->getSource('https://zds.nl/source/zds.source.json');

        $zaak = $this->entityManager->getRepository('App:ObjectEntity')->find($data['_id']);

        $zaakArray = $zaak->toArray();

        $di02Message = $this->mappingService->map($zaakArray, $toMapping);

        $encoder = new XmlEncoder();
        $message = $encoder->encode($di02Message, 'xml');

        $response = $this->callService->call($message, $source, 'POST');
        $result = $this->callService->decode($response, $source);

        $zaakArray = $this->mappingService->map($result, $fromMapping);

        $zaak->hydrate($zaakArray);

        $this->entityManager->persist($zaak);
        $this->entityManager->flush();

        return $data;
    }


}//end class
