<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Meta;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;

class MetaFileCollection implements IteratorAggregate, Countable
{
    private array  $files = [];

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

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->files);
    }

    public function count(): int
    {
        return count($this->files);
    }
}
