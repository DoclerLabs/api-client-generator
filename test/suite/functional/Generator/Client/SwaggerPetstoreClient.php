<?php

namespace Test;

use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Request\Mapper\RequestMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Handler\ResponseHandlerInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Response;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\ResponseMapperRegistryInterface;
use GuzzleHttp\ClientInterface;
use Test\Request\AddPetRequest;
use Test\Request\CountPetsRequest;
use Test\Request\DeletePetRequest;
use Test\Request\FindPetByIdRequest;
use Test\Request\FindPetsRequest;
use Test\Response\Mapper\PetCollectionResponseMapper;
use Test\Response\Mapper\PetResponseMapper;
use Test\Schema\Pet;
use Test\Schema\PetCollection;

class SwaggerPetstoreClient
{
    /** @var ClientInterface */
    private $client;
    /** @var RequestMapperInterface */
    private $requestHandler;
    /** @var ResponseHandlerInterface */
    private $responseHandler;
    /** @var ResponseMapperRegistryInterface */
    private $mapperRegistry;
    /**
     * @param ClientInterface $client
     * @param RequestMapperInterface $requestHandler
     * @param ResponseHandlerInterface $responseHandler
     * @param ResponseMapperRegistryInterface $mapperRegistry
    */
    public function __construct(ClientInterface $client, RequestMapperInterface $requestHandler, ResponseHandlerInterface $responseHandler, ResponseMapperRegistryInterface $mapperRegistry)
    {
        $this->client = $client;
        $this->requestHandler = $requestHandler;
        $this->responseHandler = $responseHandler;
        $this->mapperRegistry = $mapperRegistry;
    }
    /**
     * @param RequestInterface $request
     * @return Response
    */
    public function getResponse(RequestInterface $request) : Response
    {
        return $this->responseHandler->handle($this->client->request($request->getMethod(), $request->getRoute(), $this->requestHandler->getParameters($request)));
    }
    /**
     * @param FindPetsRequest $request
     * @return PetCollection
    */
    public function findPets(FindPetsRequest $request) : PetCollection
    {
        return $this->mapperRegistry->get(PetCollectionResponseMapper::class)->map($this->getResponse($request));
    }
    /**
     * @param AddPetRequest $request
     * @return Pet
    */
    public function addPet(AddPetRequest $request) : Pet
    {
        return $this->mapperRegistry->get(PetResponseMapper::class)->map($this->getResponse($request));
    }
    /**
     * @param CountPetsRequest $request
    */
    public function countPets(CountPetsRequest $request)
    {
        $this->getResponse($request);
    }
    /**
     * @param FindPetByIdRequest $request
     * @return Pet
    */
    public function findPetById(FindPetByIdRequest $request) : Pet
    {
        return $this->mapperRegistry->get(PetResponseMapper::class)->map($this->getResponse($request));
    }
    /**
     * @param DeletePetRequest $request
    */
    public function deletePet(DeletePetRequest $request)
    {
        $this->getResponse($request);
    }
}