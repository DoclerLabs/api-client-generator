<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Meta;

class MetaFile
{
    private string $filePath;
    private string $content;

    public function __construct(string $filePath, string $content)
    {
        $this->filePath = $filePath;
        $this->content  = $content;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
