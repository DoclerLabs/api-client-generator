<?php

declare(strict_types=1);

namespace Test\Schema\Mapper;

interface SchemaMapperInterface
{
    public function toSchema(array $payload);
}
