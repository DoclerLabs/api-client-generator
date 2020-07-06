<?php

namespace DoclerLabs\ApiClientGenerator\Output\Meta;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;

class MetaFileCollection implements IteratorAggregate
{
    private array  $files;
    private string $baseDirectory;

    public function __construct(string $baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
        $this->files         = [];
    }

    public function add(MetaFile $metaFile): void
    {
        $this->files[$metaFile->getFilePath()] = $metaFile;
    }

    public function get(string $fileName): MetaFile
    {
        if (!isset($this->files[$fileName])) {
            throw new InvalidArgumentException('Attempt to fetch non-existing template file: ' . $fileName);
        }

        return $this->files[$fileName];
    }

    /**
     * @return ArrayIterator<MetaFile>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->files);
    }

    public function getBaseDirectory(): string
    {
        return $this->baseDirectory;
    }
}
