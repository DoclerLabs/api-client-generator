<?php

namespace Test\Response\Mapper;

use DoclerLabs\ApiClientBase\Response\Mapper\ResponseMapperInterface;
use Test\Schema\Resource;
use DoclerLabs\ApiClientBase\Exception\UnexpectedResponseBodyException;
use DateTimeImmutable;
class ResourceResponseMapper implements ResponseMapperInterface
{
    const SCHEMA_NAME = Resource::class;
    /**
     * @param array $response
     * @return Resource
     * @throws UnexpectedResponseBodyException
    */
    public function map(array $response) : Resource
    {
        if (!isset($response['mandatoryInteger'], $response['mandatoryString'], $response['mandatoryEnum'], $response['mandatoryDate'])) {
            $missingFields = implode(', ', array_diff(array('mandatoryInteger', 'mandatoryString', 'mandatoryEnum', 'mandatoryDate'), array_keys($response)));
            throw new UnexpectedResponseBodyException('Required attributes for `Resource` missing in the response body: ' . $missingFields);
        }
        return new Resource($response['mandatoryInteger'], $response['mandatoryString'], $response['mandatoryEnum'], new DateTimeImmutable($response['mandatoryDate']));
    }
}