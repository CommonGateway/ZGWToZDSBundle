<?php

namespace CommonGateway\ZGWToZDSBundle\Service;

use CommonGateway\CoreBundle\Service\CallService;
use CommonGateway\CoreBundle\Service\GatewayResourceService;
use CommonGateway\CoreBundle\Service\RequestService;
use DOMDocument;
use DOMXPath;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class ZdsToZgwService
{


    public function __construct(
        private readonly GatewayResourceService $resourceService,
        private readonly CallService $callService,
        private readonly RequestService $requestService,
    ) {

    }//end __construct()


    private function getObjects(string $schema): array
    {
        // @TODO fetch (all) objects of type $schema
        return [];

    }//end getObjects()


    private function removeNamespaces($xml)
    {
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        // Remove all namespace prefixes
        $xpath = new DOMXPath($doc);
        foreach ($xpath->query('//*') as $node) {
            $node->removeAttributeNS($node->namespaceURI, $node->prefix);
        }

        return $doc->saveXML();

    }//end removeNamespaces()


    public function translateZdsToZgwZaak(array $data, array $configuration): array
    {
        $cleanXml = $this->removeNamespaces($data['crude_body']);

        $mapping = $this->resourceService->getMapping($configuration['mapping']);
        $source  = $this->resourceService->getSource($configuration['source']);

        // Decode using XmlEncoder
        $encoder = new XmlEncoder();
        $array   = $encoder->decode($cleanXml, 'xml');

        $array['_zaaktypen']     = $this->getObjects($configuration['zaaktypeSchema']);
        $array['_eigenschappen'] = $this->getObjects($configuration['eigenschapSchema']);
        $array['_roltypen']      = $this->getObjects($configuration['roltypeSchema']);

        $zaak = $this->mappingService->map($array, $mapping);

        if (isset($zaak['_headers']) === true) {
            $data['headers'] = array_merge($zaak['_headers'], $data['headers']);
            unset($zaak['_headers']);
        }

        $data['crude_body'] = json_encode($zaak);

        $data['response'] = $this->requestService->proxyHandler($data, [], $source, $data['endpoint']->getProxyOverrulesAuthentication());

        return $data;

    }//end translateZdsToZgwZaak()


    public function translateZdsToZgwDocument(array $data, array $configuration): array
    {
        $cleanXml = $this->removeNamespaces($data['crude_body']);

        $mapping = $this->resourceService->getMapping($configuration['mapping']);
        $source  = $this->resourceService->getSource($configuration['source']);

        // Decode using XmlEncoder
        $encoder = new XmlEncoder();
        $array   = $encoder->decode($cleanXml, 'xml');

        $zaakInfoObjectMapping = $this->resourceService->getMapping($configuration['mapping']);
        $zaakSource            = $this->resourceService->getSource($configuration['zaakSource']);

        $array['_documenttypen'] = $this->getObjects($configuration['zaaktypeSchema']);

        $document = $this->mappingService->map($array, $mapping);

        $data['crude_body'] = json_encode($document);

        // $response = $this->requestService->proxyHandler($data, [], $source, $data['endpoint']->getProxyOverrulesAuthentication());
        $response = $this->requestService->proxyHandler($data, [], $source, $data['endpoint']->getProxyOverrulesAuthentication());

        $document = json_decode($response->getContent(), true);

        $array['_document'] = $document;
        $array['_zaak']     = $this->callService->decodeBody($this->callService->call($zaakSource, '/zaken', 'GET', ['query' => ['identificatie' => $array['TODO_GET_IDENTIFICATION']]]));

        $caseDocument = $this->mappingService->map($array, $zaakInfoObjectMapping);

        $data['crude_body'] = json_encode($caseDocument);

        $data['response'] = $this->requestService->proxyHandler($data, [], $zaakSource);

        return $data;

    }//end translateZdsToZgwDocument()


}//end class
