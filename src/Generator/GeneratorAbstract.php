<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\ClassBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\EnumBuilder;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use DoclerLabs\ApiClientGenerator\Entity\ImportCollection;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFile;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Stmt\ClassMethod;

abstract class GeneratorAbstract implements GeneratorInterface
{
    private ImportCollection $imports;

    public function __construct(
        protected string $baseNamespace,
        protected CodeBuilder $builder,
        protected PhpVersion $phpVersion
    ) {
        $this->imports = new ImportCollection();
    }

    abstract public function generate(Specification $specification, PhpFileCollection $fileRegistry): void;

    protected function registerFile(
        PhpFileCollection $fileRegistry,
        ClassBuilder|EnumBuilder $classOrEnum,
        string $subDirectory = '',
        string $namespaceSubPath = ''
    ): void {
        $namespace = $this->withSubNamespace($namespaceSubPath);
        $fileRegistry->add(
            new PhpFile(
                sprintf('%s%s.php', $subDirectory, $classOrEnum->getName()),
                $this->fqdn($namespace, $classOrEnum->getName()),
                $this->builder->buildClass($namespace, $this->getImports(), $classOrEnum->getNode())
            )
        );

        $this->resetImports();
    }

    protected function withSubNamespace(string $namespaceSubPath): string
    {
        return sprintf('%s%s', $this->baseNamespace, $namespaceSubPath);
    }

    protected function fqdn(string $namespace, string $classOrEnum): string
    {
        return sprintf('%s\\%s', $namespace, $classOrEnum);
    }

    protected function addImport(string $fqdn, string $alias = ''): self
    {
        $import = $fqdn;
        if ($alias !== '') {
            $import = sprintf('%s as %s', $fqdn, $alias);
        }

        $this->imports->add($import);

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

    protected function generateJsonSerialize(): ClassMethod
    {
        return $this
            ->builder
            ->method('jsonSerialize')
            ->makePublic()
            ->addStmts([$this->builder->return($this->builder->localMethodCall('toArray'))])
            ->setReturnType(FieldType::PHP_TYPE_ARRAY)
            ->composeDocBlock([], FieldType::PHP_TYPE_ARRAY)
            ->getNode();
    }
}
