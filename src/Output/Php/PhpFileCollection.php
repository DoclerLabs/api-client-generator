<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Php;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;

class PhpFileCollection implements IteratorAggregate
{
    private array  $files;
    private string $baseDirectory;
    private string $baseNamespace;

    public function __construct(string $baseDirectory, string $baseNamespace)
    {
        $this->baseDirectory = $baseDirectory;
        $this->baseNamespace = $baseNamespace;
        $this->files         = [];
    }

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

    public function getBaseDirectory(): string
    {
        return $this->baseDirectory . '/src';
    }

    public function getBaseNamespace(): string
    {
        return $this->baseNamespace;
    }
}
