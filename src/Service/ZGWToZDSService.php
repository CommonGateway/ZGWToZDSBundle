<?php
/**
 * An example service for adding business logic to your class.
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace CommonGateway\ZGWToZDSBundle\Service;

use App\Event\ActionEvent;
use CommonGateway\CoreBundle\Service\CallService;
use CommonGateway\CoreBundle\Service\GatewayResourceService;
use CommonGateway\CoreBundle\Service\MappingService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
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
     * The event dispatcher.
     *
     * @var EventDispatcherInterface $eventDispatcher
     */
    private EventDispatcherInterface $eventDispatcher;


    /**
     * @param EntityManagerInterface $entityManager The Entity Manager.
     * @param LoggerInterface        $pluginLogger  The plugin version of the logger interface.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $pluginLogger,
        MappingService $mappingService,
        GatewayResourceService $resourceService,
        CallService $callService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager   = $entityManager;
        $this->logger          = $pluginLogger;
        $this->configuration   = [];
        $this->data            = [];
        $this->mappingService  = $mappingService;
        $this->resourceService = $resourceService;
        $this->callService     = $callService;
        $this->eventDispatcher = $eventDispatcher;

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
        if (isset($data['object']) === false) {
            $this->logger->warning('Object not found in the data array, action will not run', ['plugin' => 'common-gateway/zgw-to-zds-bundle']);
            return $this->data;
        }

        $this->data          = $data;
        $this->configuration = $configuration;

        $toMapping = $this->resourceService->getMapping('https://zds.nl/mapping/zds.ZgwZaakToZds.mapping.json', 'common-gateway/zgw-to-zds-bundle');
        $source    = $this->resourceService->getSource('https://zds.nl/source/zds.source.json', 'common-gateway/zgw-to-zds-bundle');

        $zaak = $this->entityManager->getRepository('App:ObjectEntity')
            ->find($data['object']['_self']['id']);

        $zaakArray = $zaak->toArray();

        $zds = $this->mappingService->mapping($toMapping, $zaakArray);

        $encoder = new XmlEncoder(['xml_root_node_name' => 'SOAP-ENV:Envelope']);
        $message = $encoder->encode($zds, 'xml');

        $response = $this->callService->call($source, '/OntvangAsynchroon', 'POST', ['body' => $message]);
        $result   = $this->callService->decodeResponse($source, $response);

        if (isset($zaakArray['zaakinformatieobjecten']) === true) {
            foreach ($zaakArray['zaakinformatieobjecten'] as $caseInformationObject) {
                $subData           = $data;
                $subData['object'] = $caseInformationObject;
                $event             = new ActionEvent('commongateway.action.event', $subData, 'simxml.document.created');
                $this->eventDispatcher->dispatch($event, 'commongateway.action.event');
            }
        }

        return $data;

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
        if (isset($data['object']) === false) {
            $this->logger->warning('Object not found in the data array, action will not run', ['plugin' => 'common-gateway/zgw-to-zds-bundle']);
            return $this->data;
        }

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
            ->find($data['object']['_self']['id']);

        $zaakArray = $data['object'];

        $di02Message = $this->mappingService->mapping($toMapping, $zaakArray);

        $encoder = new XmlEncoder(['xml_root_node_name' => 'SOAP-ENV:Envelope']);
        $message = $encoder->encode($di02Message, 'xml');

        $response = $this->callService->call(
            $source,
            $configuration['endpoint'],
            'POST',
            [
                'body'    => $message,
                'headers' => ['SOAPaction' => $configuration['SOAPaction']],
            ]
        );
        $result   = $this->callService->decodeResponse($source, $response);

        $zaakArray = $this->mappingService->mapping($fromMapping, $result);

        $zaak->hydrate($zaakArray);

        $this->entityManager->persist($zaak);
        $this->entityManager->flush();

        $data['object'] = $zaak->toArray();

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
        if (isset($data['object']) === false) {
            $this->logger->warning('Object not found in the data array, action will not run', ['plugin' => 'common-gateway/zgw-to-zds-bundle']);
            return $this->data;
        }

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

        $caseDocument = $this->entityManager->getRepository('App:ObjectEntity')
            ->find($data['object']['_self']['id']);

        $caseDocumentArray = $caseDocument->toArray();

        $documentArray = $caseDocumentArray['informatieobject'];

        $di02Message = $this->mappingService->mapping($toMapping, $documentArray);

        $encoder = new XmlEncoder(['xml_root_node_name' => 'SOAP-ENV:Envelope']);
        $message = $encoder->encode($di02Message, 'xml');

        $response = $this->callService->call(
            $source,
            $configuration['endpoint'],
            'POST',
            [
                'body'    => $message,
                'headers' => ['SOAPaction' => $configuration['SOAPaction']],
            ]
        );
        $result   = $this->callService->decodeResponse($source, $response);

        $documentArray = $this->mappingService->mapping($fromMapping, $result);

        $caseDocument->hydrate(['informatieobject' => $documentArray]);

        $this->entityManager->persist($caseDocument);
        $this->entityManager->flush();

        $data['response'] = new Response(
            \Safe\json_encode($caseDocument->toArray()),
            201,
            ['content-type' => 'application/json']
        );

        return $data;

    }//end zgwToZdsObjectIdentificationHandler()


    /**
     * Translate information objects to Lk01 messages and send them to a source.
     *
     * @param array $data          The data array
     * @param array $configuration The configuration array
     *
     * @return array The updated data array
     */
    public function zgwToZdsInformationObjectHandler(array $data, array $configuration): array
    {
        if (isset($data['object']) === false) {
            $this->logger->warning('Object not found in the data array, action will not run', ['plugin' => 'common-gateway/zgw-to-zds-bundle']);
            return $this->data;
        }

        $caseDocument = $this->entityManager->getRepository('App:ObjectEntity')
            ->find($data['object']['_self']['id']);
        $toMapping    = $this->resourceService->getMapping(
            'https://zds.nl/mapping/zds.InformatieObjectToLk02.mapping.json',
            'common-gateway/zgw-to-zds-bundle'
        );
        $source       = $this->resourceService->getSource(
            'https://zds.nl/source/zds.source.json',
            'common-gateway/zgw-to-zds-bundle'
        );

        $caseDocumentArray = $caseDocument->toArray();

        $lk01Message = $this->mappingService->mapping($toMapping, $caseDocumentArray);

        $encoder = $encoder = new XmlEncoder(['xml_root_node_name' => 'SOAP-ENV:Envelope']);
        $message = $encoder->encode($lk01Message, 'xml');

        $response = $this->callService->call(
            $source,
            $configuration['endpoint'],
            'POST',
            [
                'body'    => $message,
                'headers' => ['SOAPaction' => $configuration['SOAPaction']],
            ]
        );

        return $data;

    }//end zgwToZdsInformationObjectHandler()


    /**
     * Does an xmlEncode on the response data. (temporary solution)
     *
     * @param array $data          The data array.
     * @param array $configuration The configuration array.
     *
     * @return array The updated data array.
     */
    public function zgwToZdsXmlEncodeHandler(array $data, array $configuration): array
    {
        $response     = $data['response'];
        $responseBody = json_decode($response->getContent(), true);
        
        

        $encoder      = new XmlEncoder(['xml_root_node_name' => 'SOAP-ENV:Envelope']);
        $responseBody = $encoder->encode($responseBody, 'xml');

        $data['response'] = new Response($responseBody, $response->getStatusCode(), ['content-type' => 'text/xml']);

        return $data;

    }//end zgwToZdsXmlEncodeHandler()


}//end class
