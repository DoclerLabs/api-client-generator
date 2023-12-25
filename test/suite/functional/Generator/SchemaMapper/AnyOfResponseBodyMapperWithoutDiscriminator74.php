<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test\Schema\Mapper;

use DoclerLabs\ApiClientException\UnexpectedResponseBodyException;
use Test\Schema\GetExampleResponseBody;

class GetExampleResponseBodyMapper implements SchemaMapperInterface
{
    private AnimalMapper $animalMapper;

    private MachineMapper $machineMapper;

    public function __construct(AnimalMapper $animalMapper, MachineMapper $machineMapper)
    {
        $this->animalMapper  = $animalMapper;
        $this->machineMapper = $machineMapper;
    }

    /**
     * @throws UnexpectedResponseBodyException
     */
    public function toSchema(array $payload): GetExampleResponseBody
    {
        $schema  = new GetExampleResponseBody();
        $matches = 0;

        try {
            $schema->setAnimal($this->animalMapper->toSchema($payload));
            $matches = $matches + 1;
        } catch (UnexpectedResponseBodyException $exception) {
        }

        try {
            $schema->setMachine($this->machineMapper->toSchema($payload));
            $matches = $matches + 1;
        } catch (UnexpectedResponseBodyException $exception) {
        }
        if ($matches === 0) {
            throw new UnexpectedResponseBodyException();
        }

        return $schema;
    }
}
