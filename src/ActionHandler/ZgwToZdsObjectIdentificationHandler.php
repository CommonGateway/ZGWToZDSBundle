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


class ZgwToZdsObjectIdentificationHandler implements ActionHandlerInterface
{

    /**
     * The pet store service used by the handler
     *
     * @var ZGWToZDSService
     */
    private ZGWToZDSService $toZDSService;


    /**
     * The constructor
     *
     * @param ZGWToZDSService $toZDSService The pet store service
     */
    public function __construct(ZGWToZDSService $toZDSService)
    {
        $this->toZDSService = $toZDSService;

    }//end __construct()


    /**
     * Returns the required configuration as a https://json-schema.org array.
     *
     * @return array The configuration that this  action should comply to
     */
    public function getConfiguration(): array
    {
        return [
            '$id'         => 'https://zds.nlActionHandler/ZgwToZdsIdentificationHandler.ActionHandler.json',
            '$schema'     => 'https://docs.commongateway.nl/schemas/ActionHandler.schema.json',
            'title'       => 'Zaak Identification Handler',
            'description' =>
                'This handler creates a case identification message and sets the identification for the response.',
            'required'    => [],
            'properties'  => [
                'SOAPaction'           => [
                    'type'        => 'string',
                    'description' => 'the default SOAPaction header for this action',
                    'example'     => 'http://www.egem.nl/StUF/sector/zkn/0310/genereerDocumentIdentificatie_Di02',
                    'required'    => true,
                ],
                'endpoint'           => [
                    'type'        => 'string',
                    'description' => 'the default endpoint the action should fire to',
                    'example'     => '/VrijeBerichten',
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
        return $this->toZDSService->zgwToZdsObjectIdentificationHandler($data, $configuration);

    }//end run()


}//end class
