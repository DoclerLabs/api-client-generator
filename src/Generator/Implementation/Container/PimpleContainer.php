<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\Container;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationInterface;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use Pimple\Container;

class PimpleContainer implements ContainerImplementationInterface
{
    protected string      $baseNamespace;
    protected CodeBuilder $builder;
    protected array       $registerImports;

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

    public function getContainerInitImports(): array
    {
        return [
            'Pimple\Psr11\Container as Psr11Container',
            Container::class,
        ];
    }

    public function getContainerRegisterImports(): array
    {
        return [
            Container::class,
        ];
    }

    public function getPackages(): array
    {
        return [
            'pimple/pimple' => '^3.5',
        ];
    }

    public function registerClosure(Variable $containerVariable, Expr $key, Closure $closure): Expr
    {
        return $this->builder->assign(
            $this->builder->getArrayItem($containerVariable, $key),
            $closure
        );
    }

    public function getClosure(Variable $containerVariable, Expr $key): Expr
    {
        return $this->builder->getArrayItem($containerVariable, $key);
    }
}
