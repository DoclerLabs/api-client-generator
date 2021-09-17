<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Php;

class PhpFile
{
    private string $fileName;
    private string $fullyQualifiedClassName;
    private array  $nodes;

    public function __construct(string $fileName, string $absoluteClassName, array $nodes)
    {
        $this->fileName                = $fileName;
        $this->fullyQualifiedClassName = $absoluteClassName;
        $this->nodes                   = $nodes;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFullyQualifiedClassName(): string
    {
        return $this->fullyQualifiedClassName;
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }
}
