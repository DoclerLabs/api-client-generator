<?php

namespace Test\Response\Mapper;

use DateTimeImmutable;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Exception\UnexpectedResponseBodyException;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Mapper\ResponseMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Response;
use Test\Schema\Resource;

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