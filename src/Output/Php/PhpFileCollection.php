<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Php;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;

class PhpFileCollection implements IteratorAggregate, Countable
{
    private array $files = [];

    public function add(PhpFile $file): void
    {
        $this->files[$file->getFullyQualifiedClassName()] = $file;
    }

    public function get(string $className): PhpFile
    {
        if (!isset($this->files[$className])) {
            throw new InvalidArgumentException('Attempt to fetch non-existing php file: ' . $className);
        }

        return $this->files[$className];
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
