<?php
/**
 * An example handler for the per store.
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace CommonGateway\ZGWToZDSBundle\ActionHandler;

use CommonGateway\CoreBundle\ActionHandler\ActionHandlerInterface;
use CommonGateway\ZGWToZDSBundle\Service\ZGWToZDSService;


class ZGWToZDSHandler implements ActionHandlerInterface
{

    /**
     * The zgw to zds service used by the handler
     *
     * @var ZGWToZDSService
     */
    private ZGWToZDSService $service;


    /**
     * The constructor
     *
     * @param ZGWToZDSService $service The zgw to zds service
     */
    public function __construct(ZGWToZDSService $service)
    {
        $this->service = $service;

    }//end __construct()


    /**
     * Returns the required configuration as a https://json-schema.org array.
     *
     * @return array The configuration that this  action should comply to
     */
    public function getConfiguration(): array
    {
        return [
            '$id'         => 'https://example.com/ActionHandler/ZGWToZDSHandler.ActionHandler.json',
            '$schema'     => 'https://docs.commongateway.nl/schemas/ActionHandler.schema.json',
            'title'       => 'ZGW Zaak to ZDS ActionHandler',
            'description' => 'ZGW Zaak to ZDS',
            'required'    => [],
            'properties'  => [],
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

        return $this->service->zgwToZdsHandler($data, $configuration);

    }//end run()


}//end class
