<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\Container;

use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationInterface;
use DoclerLabs\ApiClientGenerator\Naming\CopiedNamespace;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\BodySerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use Pimple\Container;

class PimpleContainer extends RegisterContainerAbstract implements ContainerImplementationInterface
{
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

    public function getContainerInitImports(): array
    {
        return [
            'Pimple\Psr11\Container as Psr11Container',
            Container::class,
        ];
    }

    public function getContainerRegisterImports(): array
    {
        return array_merge(
            [
                Container::class,
                CopiedNamespace::getImport($this->baseNamespace, BodySerializer::class),
                CopiedNamespace::getImport($this->baseNamespace, JsonContentTypeSerializer::class),
                CopiedNamespace::getImport($this->baseNamespace, FormUrlencodedContentTypeSerializer::class),
            ],
            $this->registerImports
        );
    }

    public function getPackages(): array
    {
        return [
            'pimple/pimple' => '^3.3',
        ];
    }

    protected function registerClosure(Variable $containerVariable, Expr $key, Closure $closure): Expr
    {
        return $this->builder->assign(
            $this->builder->getArrayItem($containerVariable, $key),
            $closure
        );
    }

    protected function getClosure(Variable $containerVariable, Expr $key): Expr
    {
        return $this->builder->getArrayItem($containerVariable, $key);
    }
}