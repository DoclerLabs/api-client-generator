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
use Test\Request\AddPetRequest;
use Test\Request\CountPetsRequest;
use Test\Request\FindPetsRequest;
use Test\Request\Mapper\RequestMapperInterface;
use Test\Request\RequestInterface;
use Test\Response\ResponseHandler;
use Test\Schema\Mapper\PetCollectionMapper;
use Test\Schema\Mapper\PetMapper;
use Test\Schema\Pet;
use Test\Schema\PetCollection;

class SwaggerPetstoreClient
{
    public function __construct(private readonly ClientInterface $client, private readonly ContainerInterface $container)
    {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($this->container->get(RequestMapperInterface::class)->map($request));
    }

    public function findPets(FindPetsRequest $request): PetCollection
    {
        $response = $this->handleResponse($this->sendRequest($request));

        return $this->container->get(PetCollectionMapper::class)->toSchema($response);
    }

    public function addPet(AddPetRequest $request): Pet
    {
        $response = $this->handleResponse($this->sendRequest($request));

        return $this->container->get(PetMapper::class)->toSchema($response);
    }

    public function countPets(CountPetsRequest $request): void
    {
        $this->handleResponse($this->sendRequest($request));
    }

    protected function handleResponse(ResponseInterface $response)
    {
        return $this->container->get(ResponseHandler::class)->handle($response);
    }
}
