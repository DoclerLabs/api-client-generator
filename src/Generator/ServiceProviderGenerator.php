<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DhJasmin\StoryApiClient\Serializer\BodySerializer;
use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;

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
            ->generateRegisterMethod($compositeFields)
            ->makePrivate()
            ->setReturnType(null)
            ->getNode();

        $classBuilder = $this->builder
            ->class('ServiceProvider')
            ->addStmt($registerResponseMapperMethod);

        foreach ($this->containerImplementation->getContainerRegisterImports() as $import) {
            $this->addImport($import);
        }

        $this->registerFile($fileRegistry, $classBuilder);
    }
}
