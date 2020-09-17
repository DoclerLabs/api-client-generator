<?php

namespace Test;

use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Request\Mapper\RequestMapper;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Handler\ResponseHandler;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\ResponseMapperRegistry;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\ResponseMapperRegistryInterface;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Test\Response\Mapper\FoodResponseMapper;
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
        $registry->add(PetCollectionResponseMapper::class, static function () use($registry) : PetCollectionResponseMapper {
            return new PetCollectionResponseMapper($registry->get(PetResponseMapper::class));
        });
        $registry->add(PetResponseMapper::class, static function () use($registry) : PetResponseMapper {
            return new PetResponseMapper($registry->get(FoodResponseMapper::class));
        });
        $registry->add(FoodResponseMapper::class, static function () use($registry) : FoodResponseMapper {
            return new FoodResponseMapper();
        });
    }
}