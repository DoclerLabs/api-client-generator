<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Schema\Mapper;

use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;

interface SchemaMapperInterface
{
    /**
     * @return SerializableInterface
     */
    public function toSchema(array $payload);
}
