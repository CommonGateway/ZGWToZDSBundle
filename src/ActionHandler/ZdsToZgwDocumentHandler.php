<?php
/**
 * An example handler for the per store.
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace CommonGateway\ZGWToZDSBundle\ActionHandler;

use CommonGateway\CoreBundle\ActionHandler\ActionHandlerInterface;
use CommonGateway\ZGWToZDSBundle\Service\ZdsToZgwService;
use CommonGateway\ZGWToZDSBundle\Service\ZGWToZDSService;


class ZdsToZgwDocumentHandler implements ActionHandlerInterface
{


    /**
     * The constructor
     *
     * @param ZGWToZDSService $service The zgw to zds service
     */
    public function __construct(private readonly ZdsToZgwService $service)
    {

    }//end __construct()


    /**
     * Returns the required configuration as a https://json-schema.org array.
     *
     * @return array The configuration that this  action should comply to
     */
    public function getConfiguration(): array
    {
        return [
            '$id'         => 'https://example.com/ActionHandler/ZDSToZGWHandler.ActionHandler.json',
            '$schema'     => 'https://docs.commongateway.nl/schemas/ActionHandler.schema.json',
            'title'       => 'ZDS to ZGW Zaak ActionHandler',
            'description' => 'ZDS to ZGW Zaak',
            'required'    => [],
            'properties'  => [
                'mapping'                     => [
                    'type'        => 'string',
                    'description' => 'The default mapping of a ZDS Document to ZGW Document',
                    'example'     => 'https://zds.nl/mapping/zds.ZdsToZgwDocument.mapping.json',
                    'required'    => true,
                ],
                'source'                      => [
                    'type'        => 'string',
                    'description' => 'the default endpoint the action should fire to',
                    'example'     => 'https://zds.vng.nl/endpoints/zgw.drc.source.json',
                    'required'    => true,
                ],
                'zaakInformatieObjectMapping' => [
                    'type'        => 'string',
                    'description' => 'The default mapping of a ZDS document to ZGW zaakinformatieobject',
                    'example'     => 'https://zds.nl/mapping/zds.ZdsToZgwZaakDocument.mapping.json',
                    'required'    => true,
                ],
                'zaakSource'                  => [
                    'type'        => 'string',
                    'description' => 'the default endpoint the action should fire the zaakinformatieobject to',
                    'example'     => 'https://zds.vng.nl/endpoints/zgw.zrc.source.json',
                    'required'    => true,
                ],
                'documenttypeSchema'          => [
                    'type'        => 'string',
                    'description' => 'the schema of the ZGW Zaaktypes',
                    'example'     => 'https://vng.opencatalogi.nl/schemas/ztc.zaakType.schema.json',
                    'required'    => true,
                ],
                'outMapping'                  => [
                    'type'        => 'string',
                    'description' => 'the schema of the ZGW eigenschappen',
                    'example'     => 'https://zds.nl/mapping/DocumentToBv03.mapping.json',
                    'required'    => true,
                ],
                'zaakPath'                    => [
                    'type'        => 'string',
                    'description' => 'the default endpoint the action should fire the zaakinformatieobject to',
                    'example'     => '/zaken',
                    'required'    => true,
                ],
                'documentPath'                => [
                    'type'        => 'string',
                    'description' => 'the schema of the ZGW Zaaktypes',
                    'example'     => '/enkelvoudiginformatieobjecten',
                    'required'    => true,
                ],
                'zaakDocumentPath'            => [
                    'type'        => 'string',
                    'description' => 'the schema of the ZGW eigenschappen',
                    'example'     => '/zaakinformatieobjecten',
                    'required'    => true,
                ],

            ],
        ];

    }//end getConfiguration()


    /**
     * This function runs the service.
     *
     * @param array $data          The data from the call
     * @param array $configuration The configuration of the action
     *
     * @return array
     *
     * @SuppressWarnings("unused") Handlers ara strict implementations
     */
    public function run(array $data, array $configuration): array
    {
        return $this->service->translateZdsToZgwDocument($data, $configuration);

    }//end run()


}//end class
