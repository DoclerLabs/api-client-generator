<?php

namespace Test\Response\Mapper;

use DoclerLabs\ApiClientBase\Response\Response;
use DoclerLabs\ApiClientBase\Response\Mapper\ResponseMapperInterface;
use Test\Schema\Resource;
use DoclerLabs\ApiClientBase\Exception\UnexpectedResponseBodyException;
use DateTimeImmutable;
class ResourceResponseMapper implements ResponseMapperInterface
{
    /**
     * @param Response $response
     * @return Resource
     * @throws UnexpectedResponseBodyException
    */
    public function map(Response $response) : Resource
    {
        $payload = $response->getPayload();
        if (!isset($payload['mandatoryInteger'], $payload['mandatoryString'], $payload['mandatoryEnum'], $payload['mandatoryDate'])) {
            $missingFields = implode(', ', array_diff(array('mandatoryInteger', 'mandatoryString', 'mandatoryEnum', 'mandatoryDate'), array_keys($payload)));
            throw new UnexpectedResponseBodyException('Required attributes for `Resource` missing in the response body: ' . $missingFields);
        }
        return new Resource($payload['mandatoryInteger'], $payload['mandatoryString'], $payload['mandatoryEnum'], new DateTimeImmutable($payload['mandatoryDate']));
    }
}