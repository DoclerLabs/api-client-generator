<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

class Operation
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly Request $request,
        public readonly array $successfulResponses,
        public readonly array $errorResponses = [],
        public readonly array $tags = [],
        public readonly array $security = []
    ) {
    }
}
