<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Php;

class PhpFile
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $fullyQualifiedClassName,
        public readonly array $nodes
    ) {
    }
}
