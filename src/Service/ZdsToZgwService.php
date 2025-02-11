<?php

namespace CommonGateway\ZGWToZDSBundle\Service;

use CommonGateway\CoreBundle\Service\CacheService;
use CommonGateway\CoreBundle\Service\CallService;
use CommonGateway\CoreBundle\Service\GatewayResourceService;
use CommonGateway\CoreBundle\Service\MappingService;
use CommonGateway\CoreBundle\Service\RequestService;
use DOMDocument;
use DOMXPath;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class ZdsToZgwService
{


    public function __construct(
        private readonly GatewayResourceService $resourceService,
        private readonly CallService $callService,
        private readonly RequestService $requestService,
        private readonly CacheService $cacheService,
        private readonly MappingService $mappingService
    ) {

    }//end __construct()


    private function getObjects(string $schema): array
    {
        return $this->cacheService->searchObjectsNew([], [$schema])['results'];

    }//end getObjects()


    /**
     * Creates a response based on content.
     *
     * @param array $content The content to incorporate in the response
     * @param int   $status  The status code of the response
     *
     * @return Response
     */
    public function createResponse(array $content, int $status): Response
    {
        // $this->logger->debug('Creating XML response');
        $xmlEncoder    = new XmlEncoder(['xml_root_node_name' => 'SOAP-ENV:Envelope']);
        $contentString = $xmlEncoder->encode($content, 'xml', ['xml_encoding' => 'utf-8', 'remove_empty_tags' => true]);

        return new Response($contentString, $status);

    }//end createResponse()


    private function removeNamespaces($xml)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $newDoc = new \DOMDocument();
        $newDoc->appendChild($this->copyWithoutNamespace($doc->documentElement, $newDoc));

        return $newDoc->saveXML();

    }//end removeNamespaces()


    // Helper function to copy elements without namespaces
    private function copyWithoutNamespace(\DOMNode $node, \DOMDocument $newDoc)
    {
        // Create a new element without namespace
        $newNode = $newDoc->createElement($node->localName);

        // Copy attributes
        foreach (($node->attributes ?? []) as $attr) {
            $newNode->setAttribute($attr->name, $attr->value);
        }

        // Recursively copy child nodes
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $newNode->appendChild($newDoc->createTextNode($child->nodeValue));
            } else if ($child instanceof \DOMElement) {
                $newNode->appendChild($this->copyWithoutNamespace($child, $newDoc));
            }
        }

        return $newNode;

    }//end copyWithoutNamespace()


    public function translateZdsToZgwZaak(array $data, array $configuration): array
    {

        $cleanXml = $this->removeNamespaces($data['crude_body']);

        $mapping    = $this->resourceService->getMapping($configuration['mapping'], 'common-gateway/zgw-to-zds-bundle');
        $outMapping = $this->resourceService->getMapping($configuration['outMapping'], 'common-gateway/zgw-to-zds-bundle');
        $source     = $this->resourceService->getSource($configuration['source'], 'common-gateway/zgw-to-zds-bundle');

        // Decode using XmlEncoder
        $encoder = new XmlEncoder();
        $array   = $encoder->decode($cleanXml, 'xml');

        $array['_zaaktypen']     = $this->getObjects($configuration['zaaktypeSchema']);
        $array['_eigenschappen'] = $this->getObjects($configuration['eigenschapSchema']);
        $array['_roltypen']      = $this->getObjects($configuration['roltypeSchema']);

        $zaak = $this->mappingService->mapping($mapping, $array);

        if (isset($zaak['_headers']) === true) {
            $data['headers'] = array_merge($zaak['_headers'], $data['headers']);
            unset($zaak['_headers']);
        }

        $data['crude_body'] = json_encode($zaak);

        $data['path']['{route}'] = ltrim($configuration['path'], '/');
        $response                = json_decode($this->requestService->proxyHandler($data, [], $source, $data['endpoint']->getProxyOverrulesAuthentication())->getContent(), true);

        $data['response'] = $this->createResponse($this->mappingService->mapping($outMapping, $response), 200);

        return $data;

    }//end translateZdsToZgwZaak()


    public function translateZdsToZgwDocument(array $data, array $configuration): array
    {
        $cleanXml = $this->removeNamespaces($data['crude_body']);

        $mapping    = $this->resourceService->getMapping($configuration['mapping'], 'common-gateway/zgw-to-zds-bundle');
        $source     = $this->resourceService->getSource($configuration['source'], 'common-gateway/zgw-to-zds-bundle');
        $outMapping = $this->resourceService->getMapping($configuration['outMapping'], 'common-gateway/zgw-to-zds-bundle');

        // Decode using XmlEncoder
        $encoder = new XmlEncoder();
        $array   = $encoder->decode($cleanXml, 'xml');

        $zaakInfoObjectMapping = $this->resourceService->getMapping($configuration['zaakInformatieObjectMapping'], 'common-gateway/zgw-to-zds-bundle');
        $zaakSource            = $this->resourceService->getSource($configuration['zaakSource'], 'common-gateway/zgw-to-zds-bundle');

        $array['_documenttypen'] = $this->getObjects($configuration['documenttypeSchema']);

        $document = $this->mappingService->mapping($mapping, $array);

        $data['crude_body']      = json_encode($document);
        $data['path']['{route}'] = ltrim($configuration['documentPath'], '/');

        $response = $this->requestService->proxyHandler($data, [], $source, $data['endpoint']->getProxyOverrulesAuthentication());

        $document = json_decode($response->getContent(), true);

        $array['_document'] = $document;
        $array['_zaak']     = $this->callService->decodeResponse($zaakSource, $this->callService->call($zaakSource, '/'.ltrim($configuration['zaakPath'], '/'), 'GET', ['query' => ['identificatie' => $array['Body']['edcLk01']['object']['isRelevantVoor']['gerelateerde']['identificatie']]]))['results'][0];

        $caseDocument = $this->mappingService->mapping($zaakInfoObjectMapping, $array);

        $data['crude_body'] = json_encode($caseDocument);

        $data['path']['{route}'] = ltrim($configuration['zaakDocumentPath'], '/');
        $response                = json_decode($this->requestService->proxyHandler($data, [], $zaakSource)->getContent(), true);

        $data['response'] = $this->createResponse($this->mappingService->mapping($outMapping, $response), 200);
        return $data;

    }//end translateZdsToZgwDocument()


    public function identificatieActionHandler(array $data, array $configuration): array
    {
        $mappingOut       = $this->resourceService->getMapping($configuration['mapping'], 'common-gateway/zgw-to-zds-bundle');
        $data['response'] = $this->createResponse($this->mappingService->mapping($mappingOut, $data['body']), 200);

        return $data;

    }//end identificatieActionHandler()


}//end class
