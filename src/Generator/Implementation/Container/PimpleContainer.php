<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\Container;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationInterface;
use DoclerLabs\ApiClientGenerator\Generator\ResponseMapperGenerator;
use DoclerLabs\ApiClientGenerator\Naming\ResponseMapperNaming;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Pimple\Container;

class PimpleContainer implements ContainerImplementationInterface
{
    protected string      $baseNamespace;
    protected CodeBuilder $builder;
    private array         $registerImports;

    public function __construct(string $baseNamespace, CodeBuilder $builder)
    {
        $this->baseNamespace = $baseNamespace;
        $this->builder       = $builder;
    }

    public function generateInitContainerMethod(): MethodBuilder
    {
        $statements = [];

        $pimpleContainerVariable = $this->builder->var('pimpleContainer');
        $statements[]            = $this->builder->assign(
            $pimpleContainerVariable,
            $this->builder->new('Container')
        );

        $containerVariable = $this->builder->var('container');
        $statements[]      = $this->builder->assign(
            $containerVariable,
            $this->builder->new('Psr11Container', [$pimpleContainerVariable])
        );

        $serviceProviderVariable = $this->builder->var('serviceProvider');
        $statements[]            = $this->builder->assign(
            $serviceProviderVariable,
            $this->builder->new('ServiceProvider', [])
        );

        $statements[] = $this->builder->methodCall(
            $serviceProviderVariable,
            'register',
            [$pimpleContainerVariable]
        );

        $statements[] = $this->builder->return($containerVariable);

        return $this->builder
            ->method('initContainer')
            ->addStmts($statements);
    }

    public function generateRegisterResponseMappers(array $compositeFields): MethodBuilder
    {
        $statements = [];

        $param = $this->builder
            ->param('container')
            ->setType('Container')
            ->getNode();

        $containerVariable = $this->builder->var('container');
        foreach ($compositeFields as $field) {
            /** @var Field $field */
            $closureStatements       = [];
            $mapperClass             = ResponseMapperNaming::getClassName($field);
            $this->registerImports[] = sprintf(
                '%s%s\\%s',
                $this->baseNamespace,
                ResponseMapperGenerator::NAMESPACE_SUBPATH,
                $mapperClass
            );

            $mapperClassConst = $this->builder->classConstFetch($mapperClass, 'class');

            $closureStatements[] = $this->builder->return($this->buildMapperDependencies($field, $containerVariable));

            $closure = $this->builder->closure($closureStatements, [], [$containerVariable], $mapperClass);

            $statements[] = $this->builder->assign(
                $this->builder->getArrayItem($containerVariable, $mapperClassConst),
                $closure
            );
        }

        return $this->builder
            ->method('registerResponseMappers')
            ->addParam($param)
            ->addStmts($statements)
            ->composeDocBlock([$param], '', []);
    }

    public function getContainerInitImports(): array
    {
        return [
            'Pimple\Psr11\Container as Psr11Container',
            Container::class,
        ];
    }

    public function getContainerRegisterImports(): array
    {
        $this->registerImports[] = Container::class;

        return $this->registerImports;
    }

    public function getPackages(): array
    {
        return [
            'pimple/pimple' => '^3.3',
        ];
    }

    private function buildMapperDependencies(Field $field, Variable $containerVariable): New_
    {
        $dependencies = [];
        if ($field->isObject()) {
            $alreadyInjected = [];
            foreach ($field->getObjectProperties() as $subfield) {
                if ($subfield->isComposite() && !isset($alreadyInjected[$subfield->getPhpClassName()])) {
                    $getMethodArg   = $this->builder->classConstFetch(
                        ResponseMapperNaming::getClassName($subfield),
                        'class'
                    );
                    $dependencies[] = $this->builder->getArrayItem($containerVariable, $getMethodArg);

                    $alreadyInjected[$subfield->getPhpClassName()] = true;
                }
            }
        }
        if ($field->isArrayOfObjects()) {
            $getMethodArg   = $this->builder->classConstFetch(
                ResponseMapperNaming::getClassName($field->getArrayItem()),
                'class'
            );
            $dependencies[] = $this->builder->getArrayItem($containerVariable, $getMethodArg);
        }

        return $this->builder->new(ResponseMapperNaming::getClassName($field), $dependencies);
    }
}