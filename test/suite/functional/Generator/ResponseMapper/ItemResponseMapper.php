<?php

namespace Test\Response\Mapper;

use DateTimeImmutable;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Exception\UnexpectedResponseBodyException;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Mapper\ResponseMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Response;
use Test\Schema\Item;

class ItemResponseMapper implements ResponseMapperInterface
{
    /** @var EmbeddedObjectResponseMapper */
    private $embeddedObjectResponseMapper;
    /**
     * @param EmbeddedObjectResponseMapper $embeddedObjectResponseMapper
    */
    public function __construct(EmbeddedObjectResponseMapper $embeddedObjectResponseMapper)
    {
        $this->embeddedObjectResponseMapper = $embeddedObjectResponseMapper;
    }
    /**
     * @param Response $response
     * @return Item
     * @throws UnexpectedResponseBodyException
    */
    public function map(Response $response) : Item
    {
        $payload = $response->getPayload();
        if (!isset($payload['mandatoryInteger'], $payload['mandatoryString'], $payload['mandatoryEnum'], $payload['mandatoryDate'], $payload['mandatoryFloat'], $payload['mandatoryBoolean'], $payload['mandatoryArray'], $payload['mandatoryObject'])) {
            $missingFields = implode(', ', array_diff(array('mandatoryInteger', 'mandatoryString', 'mandatoryEnum', 'mandatoryDate', 'mandatoryFloat', 'mandatoryBoolean', 'mandatoryArray', 'mandatoryObject'), array_keys($payload)));
            throw new UnexpectedResponseBodyException('Required attributes for `Item` missing in the response body: ' . $missingFields);
        }
        $schema = new Item($payload['mandatoryInteger'], $payload['mandatoryString'], $payload['mandatoryEnum'], new DateTimeImmutable($payload['mandatoryDate']), $payload['mandatoryFloat'], $payload['mandatoryBoolean'], $payload['mandatoryArray'], $this->embeddedObjectResponseMapper->map(new Response($response->getStatusCode(), $payload['mandatoryObject'])));
        if (isset($payload['optionalInteger'])) {
            $schema->setOptionalInteger($payload['optionalInteger']);
        }
        if (isset($payload['optionalString'])) {
            $schema->setOptionalString($payload['optionalString']);
        }
        if (isset($payload['optionalEnum'])) {
            $schema->setOptionalEnum($payload['optionalEnum']);
        }
        if (isset($payload['optionalDate'])) {
            $schema->setOptionalDate(new DateTimeImmutable($payload['optionalDate']));
        }
        if (isset($payload['optionalFloat'])) {
            $schema->setOptionalFloat($payload['optionalFloat']);
        }
        if (isset($payload['optionalBoolean'])) {
            $schema->setOptionalBoolean($payload['optionalBoolean']);
        }
        if (isset($payload['optionalArray'])) {
            $schema->setOptionalArray($payload['optionalArray']);
        }
        if (isset($payload['optionalObject'])) {
            $schema->setOptionalObject($this->embeddedObjectResponseMapper->map(new Response($response->getStatusCode(), $payload['optionalObject'])));
        }
        return $schema;
    }
}