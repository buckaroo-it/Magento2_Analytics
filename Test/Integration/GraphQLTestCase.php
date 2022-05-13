<?php

namespace Buckaroo\Magento2\Test\Integration;

use Magento\Framework\GraphQl\Query\Fields as QueryFields;
use Magento\Framework\Module\Manager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQl\Service\GraphQlRequest;

class GraphQLTestCase extends IntegrationTestCase
{
    /**
     * @var SerializerInterface
     */
    protected $json;

    /**
     * @var GraphQlRequest
     */
    protected $graphQlRequest;

    protected function setUpWithoutVoid()
    {
        /** @var Manager $moduleManager */
        $moduleManager = $this->objectManager->get(Manager::class);
        if (!$moduleManager->isEnabled('Magento_GraphQl')) {
            $this->markTestSkipped('Module Magento_GraphQl is not enabled');
        }

        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
    }

    /**
     * @param $query
     * @return mixed
     * @throws \Exception
     */
    protected function graphQlQuery($query)
    {
        $this->resetGraphQlCache();
        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());

        if (isset($responseData['errors'])) {
            $this->processErrors($responseData);
        }

        return $responseData['data'];
    }

    /**
     * @param $body
     * @throws \Exception
     */
    private function processErrors($body)
    {
        $errorMessage = '';
        foreach ($body['errors'] as $error) {
            if (!isset($error['message'])) {
                continue;
            }

            $errorMessage .= $error['message'] . PHP_EOL;
            if (isset($error['debugMessage'])) {
                $errorMessage .= $error['debugMessage'] . PHP_EOL;
            }
        }

        throw new \Exception('GraphQL response contains errors: ' . $errorMessage);
    }

    private function resetGraphQlCache()
    {
        $this->objectManager->removeSharedInstance(GraphQl::class);
        $this->objectManager->removeSharedInstance(QueryFields::class);
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
    }
}