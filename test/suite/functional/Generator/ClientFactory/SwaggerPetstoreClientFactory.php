<?php

namespace Test;

use GuzzleHttp\Client;
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
     * @param array $options
     * @return SwaggerPetstoreClient
    */
    function create(string $baseUri, array $options = array()) : SwaggerPetstoreClient
    {
        if (substr($baseUri, -1) !== '/') {
            throw new InvalidArgumentException('Base URI should end with the `/` symbol.');
        }
        $default = array('base_uri' => $baseUri, 'timeout' => 3, 'headers' => array('Accept' => 'application/json', 'Content-Type' => 'application/json', 'X-Client-Ip' => $_SERVER['HTTP_X_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? null), 'http_errors' => false);
        $config = array_replace_recursive($default, $options);
        $registry = new ResponseMapperRegistry();
        $this->registerResponseMappers($registry);
        return new SwaggerPetstoreClient(new Client($config), new RequestMapper(), new ResponseHandler(), $registry);
    }
    /**
     * @param ResponseMapperRegistryInterface $registry
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