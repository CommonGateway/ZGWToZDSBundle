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


class ZdsIdHandler implements ActionHandlerInterface
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
            'title'       => 'ZDS ID ActionHandler',
            'description' => 'ZDS Di02 to Du02',
            'required'    => [],
            'properties'  => [
                'mapping' => [
                    'type'        => 'string',
                    'description' => 'The default mapping of a ZDS Document to ZGW Document',
                    'example'     => 'https://zds.nl/mapping/ZaakDi02ToDu02.mapping.json',
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
        return $this->service->identificatieActionHandler($data, $configuration);

    }//end run()


}//end class
