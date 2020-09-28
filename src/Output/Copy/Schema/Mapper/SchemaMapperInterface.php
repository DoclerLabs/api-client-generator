<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Schema\Mapper;

interface SchemaMapperInterface
{
    public function toSchema(array $payload);
}
