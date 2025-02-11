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


class ZdsToZgwZaakHandler implements ActionHandlerInterface
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
                'mapping' => [
                    'type'        => 'string',
                    'description' => 'The default mapping of a ZDS zaak to ZGW rol',
                    'example'     => 'https://zds.nl/mapping/zds.ZdsToZgwRol.mapping.json',
                    'required'    => true,
                ],
                'source'   => [
                    'type'        => 'string',
                    'description' => 'the default endpoint the action should fire to',
                    'example'     => 'https://zds.vng.nl/endpoints/zgw.zrc.source.json',
                    'required'    => true,
                ],
                'zaaktypeSchema' => [
                    'type'        => 'string',
                    'description' => 'the schema of the ZGW Zaaktypes',
                    'example'     => 'https://vng.opencatalogi.nl/schemas/ztc.zaakType.schema.json',
                    'required'    => true
                ],
                'eigenschapSchema' => [
                    'type'        => 'string',
                    'description' => 'the schema of the ZGW eigenschappen',
                    'example'     => 'https://vng.opencatalogi.nl/schemas/ztc.eigenschap.schema.json',
                    'required'    => true
                ],
                'roltypeSchema' => [
                    'type'        => 'string',
                    'description' => 'the schema of the ZGW eigenschappen',
                    'example'     => 'https://vng.opencatalogi.nl/schemas/ztc.rolType.schema.json',
                    'required'    => true
                ],
                'outMapping' => [
                    'type'        => 'string',
                    'description' => 'the schema of the ZGW eigenschappen',
                    'example'     => 'https://zds.nl/mapping/RolToBv03.mapping.json',
                    'required'    => true
                ],
                'path'   => [
                    'type'        => 'string',
                    'description' => 'the default endpoint the action should fire the zaakinformatieobject to',
                    'example'     => '/rollen',
                    'required'    => true,
                ]
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
        return $this->service->translateZdsToZgwZaak($data, $configuration);

    }//end run()


}//end class
