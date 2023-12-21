<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Meta;

class MetaFile
{
    public function __construct(public readonly string $filePath, public readonly string $content)
    {
    }
}
