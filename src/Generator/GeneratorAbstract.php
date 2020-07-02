<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Builder\ClassBuilder;
use DoclerLabs\ApiClientGenerator\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Builder\ImportCollection;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFile;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;

abstract class GeneratorAbstract implements GeneratorInterface
{
    protected CodeBuilder    $builder;
    private ImportCollection $imports;

    public function __construct(CodeBuilder $builder)
    {
        $this->imports = new ImportCollection();
        $this->builder = $builder;
    }

    abstract public function generate(Specification $specification, PhpFileCollection $fileRegistry): void;

    protected function registerFile(
        PhpFileCollection $fileRegistry,
        ClassBuilder $class,
        string $subDirectory = '',
        string $namespaceSubPath = ''
    ): void {
        $namespace = sprintf('%s%s', $fileRegistry->getBaseNamespace(), $namespaceSubPath);
        $fileRegistry->add(
            new PhpFile(
                sprintf('%s%s.php', $subDirectory, $class->getName()),
                sprintf('%s\\%s', $namespace, $class->getName()),
                $this->builder->buildClass($namespace, $this->getImports(), $class->getNode())
            )
        );

        $this->resetImports();
    }

    protected function addImport(string $fqdn): self
    {
        $this->imports->add($fqdn);

        return $this;
    }

    protected function getImports(): ImportCollection
    {
        return $this->imports;
    }

    protected function resetImports(): void
    {
        $this->imports = new ImportCollection();
    }
}
