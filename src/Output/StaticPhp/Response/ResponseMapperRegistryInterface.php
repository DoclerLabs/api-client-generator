<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response;

use Closure;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Mapper\ResponseMapperInterface;

interface ResponseMapperRegistryInterface
{
    public function add(string $schemaName, Closure $mapper);

    public function get(string $schemaName): ResponseMapperInterface;
}
