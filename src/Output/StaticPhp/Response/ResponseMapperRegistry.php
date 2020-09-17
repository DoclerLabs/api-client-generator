<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response;

use Closure;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Mapper\ResponseMapperInterface;
use InvalidArgumentException;

class ResponseMapperRegistry implements ResponseMapperRegistryInterface
{
    /** @var Closure[] */
    private $mappers = [];

    public function add(string $schemaName, Closure $mapper)
    {
        if (isset($this->mappers[$schemaName])) {
            throw new InvalidArgumentException('Mapper was added twice: ' . $schemaName);
        }

        $this->mappers[$schemaName] = $mapper;
    }

    public function get(string $schemaName): ResponseMapperInterface
    {
        if (!isset($this->mappers[$schemaName])) {
            throw new InvalidArgumentException('Unregistered response mapper: ' . $schemaName);
        }

        return $this->mappers[$schemaName]();
    }
}
