<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test;

use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Test\Request\AddPetTaskRequest;
use Test\Request\Mapper\RequestMapperInterface;
use Test\Request\RequestInterface;
use Test\Request\SavePetRequest;
use Test\Response\ResponseHandler;
use Test\Schema\Mapper\PetMapper;
use Test\Schema\Mapper\PetTaskMapper;
use Test\Schema\Pet;

class MultipleResponsesClient
{
    private ClientInterface $client;

    private ContainerInterface $container;

    public function __construct(ClientInterface $client, ContainerInterface $container)
    {
        $this->client    = $client;
        $this->container = $container;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($this->container->get(RequestMapperInterface::class)->map($request));
    }

    public function savePet(SavePetRequest $request): ?Pet
    {
        $response             = $this->sendRequest($request);
        $unserializedResponse = $this->handleResponse($response);
        switch ($response->getStatusCode()) {
            case 204:
                return null;
            case 200:
            case 201:
                return $this->container->get(PetMapper::class)->toSchema($unserializedResponse);
        }
        throw new RuntimeException('Response status code not properly mapped in schema.');
    }

    public function addPetTask(AddPetTaskRequest $request)
    {
        $response             = $this->sendRequest($request);
        $unserializedResponse = $this->handleResponse($response);
        switch ($response->getStatusCode()) {
            case 204:
                return null;
            case 200:
            case 201:
                return $this->container->get(PetMapper::class)->toSchema($unserializedResponse);
            case 202:
                return $this->container->get(PetTaskMapper::class)->toSchema($unserializedResponse);
        }
        throw new RuntimeException('Response status code not properly mapped in schema.');
    }

    protected function handleResponse(ResponseInterface $response)
    {
        return $this->container->get(ResponseHandler::class)->handle($response);
    }
}
