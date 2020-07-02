<?php

namespace Test;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use DoclerLabs\ApiClientBase\Response\Handler\ResponseHandler;
use DoclerLabs\ApiClientBase\Request\Mapper\RequestMapper;
use DoclerLabs\ApiClientBase\Response\ResponseMapperRegistry;
use DoclerLabs\ApiClientBase\Response\ResponseMapperRegistryInterface;
use InvalidArgumentException;
use Test\Response\Mapper\PetCollectionResponseMapper;
use Test\Response\Mapper\PetResponseMapper;
class SwaggerPetstoreClientFactory
{
    /**
     * @param string $baseUri
     * @param float $connectionTimeout
     * @param float $requestTimeout
     * @param HandlerStack $handlerStack
     * @param string $proxy
     * @return SwaggerPetstoreClient
    */
    function create(string $baseUri, float $connectionTimeout, float $requestTimeout, HandlerStack $handlerStack = null, string $proxy = null) : SwaggerPetstoreClient
    {
        if (substr($baseUri, -1) !== '/') {
            throw new InvalidArgumentException('Base URI should end with the `/` symbol.');
        }
        $config = array('base_uri' => $baseUri, 'handler' => $handlerStack, 'timeout' => $requestTimeout, 'connect_timeout' => $connectionTimeout, 'proxy' => $proxy, 'http_errors' => false, 'headers' => array('Accept' => 'application/json', 'Content-Type' => 'application/json'));
        $registry = new ResponseMapperRegistry();
        $this->registerResponseMappers($registry);
        return new SwaggerPetstoreClient(new Client($config), new RequestMapper(), new ResponseHandler(), $registry);
    }
    /**
     * @param ResponseMapperRegistryInterface $registry
     * @codeCoverageIgnore
    */
    function registerResponseMappers(ResponseMapperRegistryInterface $registry)
    {
        $registry->add(PetCollectionResponseMapper::SCHEMA_NAME, static function () : PetCollectionResponseMapper {
            return new PetCollectionResponseMapper(new PetResponseMapper(new FoodResponseMapper()));
        });
        $registry->add(PetResponseMapper::SCHEMA_NAME, static function () : PetResponseMapper {
            return new PetResponseMapper(new FoodResponseMapper());
        });
    }
}