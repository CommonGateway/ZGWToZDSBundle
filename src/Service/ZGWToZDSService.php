<?php
/**
 * An example service for adding business logic to your class.
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace CommonGateway\ZGWToZDSBundle\Service;

use CommonGateway\CoreBundle\Service\CallService;
use CommonGateway\CoreBundle\Service\GatewayResourceService;
use CommonGateway\CoreBundle\Service\MappingService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class ZGWToZDSService
{

    /**
     * Configuration for handlers.
     *
     * @var array $configuration
     */
    private array $configuration;

    /**
     * Data for handlers.
     *
     * @var array $data
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
        $this->callService     = $callService;

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


    /**
     * Creates a ZDS Di02 call to the ZDS source, and takes the identification in the respons as case identifier
     *
     * @param array $data          The data from the response.
     * @param array $configuration The configuration for this action.
     *
     * @return array The resulting data array.
     */
    public function zgwToZdsIdentificationHandler(array $data, array $configuration): array
    {
        $this->configuration = $configuration;

        $toMapping   = $this->resourceService->getMapping(
            'https://zds.nl/mapping/zds.zgwZaakToDi02.mapping.json',
            'common-gateway/zgw-to-zds-bundle'
        );
        $fromMapping = $this->resourceService->getMapping(
            'https://zds.nl/mapping/zds.Du02ToZgwZaak.mapping.json',
            'common-gateway/zgw-to-zds-bundle'
        );
        $source      = $this->resourceService->getSource(
            'https://zds.nl/source/zds.source.json',
            'common-gateway/zgw-to-zds-bundle'
        );

        $zaak = $this->entityManager->getRepository('App:ObjectEntity')
            ->find(\Safe\json_decode($data['response']->getContent(), true)['_id']);

        $zaakArray = \Safe\json_decode($data['response']->getContent(), true);

        $di02Message = $this->mappingService->mapping($toMapping, $zaakArray);

        $encoder = new XmlEncoder(['xml_root_node_name' => 'SOAP-ENV:Envelope']);
        $message = $encoder->encode($di02Message, 'xml');

        $response = $this->callService->call(
            $source,
            $configuration['endpoint'],
            'POST',
            [
                'body' => $message,
                'headers' => ['SOAPaction' => $configuration['SOAPaction']]
            ]
        );
        $result   = $this->callService->decodeResponse($source, $response);

        $zaakArray = $this->mappingService->mapping($fromMapping, $result);

        $zaak->hydrate($zaakArray);

        $this->entityManager->persist($zaak);
        $this->entityManager->flush();

        $data['response'] = new Response(
            \Safe\json_encode($zaak->toArray()),
            201,
            ['content-type' => 'application/json']
        );

        return $data;

    }//end zgwToZdsIdentificationHandler()


    /**
     * Creates a ZDS Di02 call to the ZDS source, and takes the identification in the respons as case identifier
     *
     * @param array $data          The data from the response.
     * @param array $configuration The configuration for this action.
     *
     * @return array The resulting data array.
     */
    public function zgwToZdsObjectIdentificationHandler(array $data, array $configuration): array
    {

        $this->configuration = $configuration;

        $toMapping   = $this->resourceService->getMapping(
                'https://zds.nl/mapping/zds.InformatieObjectToDi02.mapping.json',
                'common-gateway/zgw-to-zds-bundle'
            );
        $fromMapping = $this->resourceService->getMapping(
                'https://zds.nl/mapping/zds.Du02ToZgwInformatieObject.mapping.json',
                'common-gateway/zgw-to-zds-bundle'
            );
        $source      = $this->resourceService->getSource(
                'https://zds.nl/source/zds.source.json',
                'common-gateway/zgw-to-zds-bundle'
            );

        $document = $this->entityManager->getRepository('App:ObjectEntity')
            ->find(\Safe\json_decode($data['response']->getContent(), true)['_id']);

        $documentArray = \Safe\json_decode($data['response']->getContent(), true);

        $di02Message = $this->mappingService->mapping($toMapping, $documentArray);

        $encoder = new XmlEncoder(['xml_root_node_name' => 'SOAP-ENV:Envelope']);
        $message = $encoder->encode($di02Message, 'xml');

        $response = $this->callService->call(
            $source,
            $configuration['endpoint'],
            'POST',
            [
                'body' => $message,
                'headers' => ['SOAPaction' => $configuration['SOAPaction']]
            ]
        );
        $result   = $this->callService->decodeResponse($source, $response);

        $documentArray = $this->mappingService->mapping($fromMapping, $result);

        $document->hydrate($documentArray);

        $this->entityManager->persist($document);
        $this->entityManager->flush();

        $data['response'] = new Response(
            \Safe\json_encode($document->toArray()),
            201,
            ['content-type' => 'application/json']
        );

        return $data;

    }//end zgwToZdsIdentificationHandler()


}//end class
