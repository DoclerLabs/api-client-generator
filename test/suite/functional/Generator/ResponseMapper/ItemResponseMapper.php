<?php

namespace Test\Response\Mapper;

use DoclerLabs\ApiClientBase\Response\Mapper\ResponseMapperInterface;
use Test\Schema\Item;
use DoclerLabs\ApiClientBase\Exception\UnexpectedResponseBodyException;
use DateTimeImmutable;
class ItemResponseMapper implements ResponseMapperInterface
{
    const SCHEMA_NAME = Item::class;
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
     * @param array $response
     * @return Item
     * @throws UnexpectedResponseBodyException
    */
    public function map(array $response) : Item
    {
        if (!isset($response['mandatoryInteger'], $response['mandatoryString'], $response['mandatoryEnum'], $response['mandatoryDate'], $response['mandatoryFloat'], $response['mandatoryBoolean'], $response['mandatoryArray'], $response['mandatoryObject'])) {
            $missingFields = implode(', ', array_diff(array('mandatoryInteger', 'mandatoryString', 'mandatoryEnum', 'mandatoryDate', 'mandatoryFloat', 'mandatoryBoolean', 'mandatoryArray', 'mandatoryObject'), array_keys($response)));
            throw new UnexpectedResponseBodyException('Required attributes for `Item` missing in the response body: ' . $missingFields);
        }
        $schema = new Item($response['mandatoryInteger'], $response['mandatoryString'], $response['mandatoryEnum'], DateTimeImmutable::createFromFormat(DATE_RFC3339, $response['mandatoryDate']), $response['mandatoryFloat'], $response['mandatoryBoolean'], $response['mandatoryArray'], $this->embeddedObjectResponseMapper->map($response['mandatoryObject']));
        if (isset($response['optionalInteger'])) {
            $schema->setOptionalInteger($response['optionalInteger']);
        }
        if (isset($response['optionalString'])) {
            $schema->setOptionalString($response['optionalString']);
        }
        if (isset($response['optionalEnum'])) {
            $schema->setOptionalEnum($response['optionalEnum']);
        }
        if (isset($response['optionalDate'])) {
            $schema->setOptionalDate(DateTimeImmutable::createFromFormat(DATE_RFC3339, $response['optionalDate']));
        }
        if (isset($response['optionalFloat'])) {
            $schema->setOptionalFloat($response['optionalFloat']);
        }
        if (isset($response['optionalBoolean'])) {
            $schema->setOptionalBoolean($response['optionalBoolean']);
        }
        if (isset($response['optionalArray'])) {
            $schema->setOptionalArray($response['optionalArray']);
        }
        if (isset($response['optionalObject'])) {
            $schema->setOptionalObject($this->embeddedObjectResponseMapper->map($response['optionalObject']));
        }
        return $schema;
    }
}