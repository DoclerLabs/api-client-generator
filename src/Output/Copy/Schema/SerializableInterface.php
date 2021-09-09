<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Schema;

interface SerializableInterface
{
    public function toArray(): array;
}
