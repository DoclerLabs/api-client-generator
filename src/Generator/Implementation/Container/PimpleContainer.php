<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\Container;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationInterface;
use Pimple\Psr11\Container;

class PimpleContainer implements ContainerImplementationInterface
{
    protected CodeBuilder $builder;

    public function __construct(CodeBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function generateInitContainerMethod(): MethodBuilder
    {
        $statements        = [];
        $containerVariable = $this->builder->var('container');
        $statements[]      = $this->builder->assign(
            $containerVariable,
            $this->builder->new('Container')
        );

        $serviceProviderVariable = $this->builder->var('serviceProvider');
        $statements[]            = $this->builder->assign(
            $serviceProviderVariable,
            $this->builder->new('ServiceProvider')
        );

        $statements[] = $this->builder->methodCall(
            $serviceProviderVariable,
            'registerResponseMappers',
            [$containerVariable]
        );

        $statements[] = $this->builder->return($containerVariable);

        return $this->builder
            ->method('initContainer')
            ->addStmts($statements);
    }

    public function getInitContainerImports(): array
    {
        return [
            Container::class,
        ];
    }

    public function getPackages(): array
    {
        return [
            'pimple/pimple' => '^3.0',
        ];
    }
}