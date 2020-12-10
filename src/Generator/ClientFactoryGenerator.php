<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ClientNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Stmt\ClassMethod;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;

class ClientFactoryGenerator extends GeneratorAbstract
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
        $className = ClientNaming::getFactoryClassName($specification);

        $this
            ->addImport(ClientInterface::class)
            ->addImport(ContainerInterface::class);

        foreach ($this->containerImplementation->getContainerInitImports() as $import) {
            $this->addImport($import);
        }

        $initContainerMethod = $this->containerImplementation
            ->generateInitContainerMethod()
            ->makePrivate()
            ->setReturnType('ContainerInterface')
            ->getNode();

        $classBuilder = $this->builder
            ->class($className)
            ->addStmt($this->generateCreate($specification))
            ->addStmt($initContainerMethod);

        $this->registerFile($fileRegistry, $classBuilder);
    }

    protected function generateCreate(Specification $specification): ClassMethod
    {
        $params   = [];
        $params[] = $this->builder
            ->param('client')
            ->setType('ClientInterface')
            ->getNode();

        $clientClassName = ClientNaming::getClassName($specification);
        $statements[]    = $this->builder->return(
            $this->builder->new(
                $clientClassName,
                $this->builder->args(
                    [
                        $this->builder->var('client'),
                        $this->builder->localMethodCall('initContainer'),
                    ]
                )
            )
        );

        return $this->builder
            ->method('create')
            ->addParams($params)
            ->addStmts($statements)
            ->setReturnType($clientClassName)
            ->composeDocBlock($params, $clientClassName, [])
            ->getNode();
    }
}
