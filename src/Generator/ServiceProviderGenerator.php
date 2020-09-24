<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Stmt\ClassMethod;

class ServiceProviderGenerator extends GeneratorAbstract
{
    private ContainerImplementationStrategy $containerImplementation;

    public function __construct(
        string $baseNamespace,
        CodeBuilder $builder,
        ContainerImplementationStrategy $containerImplementation
    ) {
        parent::__construct($baseNamespace, $builder);
        $this->containerImplementation = $containerImplementation;
    }

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $compositeFields = $specification->getCompositeResponseFields()->getUniqueByPhpClassName();

        $registerResponseMapperMethod = $this->containerImplementation
            ->generateRegisterResponseMappers($compositeFields)
            ->makePrivate()
            ->setReturnType(null)
            ->getNode();

        $classBuilder = $this->builder
            ->class('ServiceProvider')
            ->addStmt($this->generateRegister())
            ->addStmt($registerResponseMapperMethod);

        foreach ($this->containerImplementation->getContainerRegisterImports() as $import) {
            $this->addImport($import);
        }

        $this->registerFile($fileRegistry, $classBuilder);
    }

    public function generateRegister(): ClassMethod
    {
        $param = $this->builder
            ->param('container')
            ->setType('Container')
            ->getNode();

        return $this->builder
            ->method('register')
            ->addParam($param)
            ->addStmt(
                $this->builder->localMethodCall('registerResponseMappers', [$this->builder->var('container')])
            )->getNode();
    }
}
