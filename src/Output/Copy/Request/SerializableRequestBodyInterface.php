<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Request;

interface SerializableRequestBodyInterface
{
    public function toArray(): array;
}