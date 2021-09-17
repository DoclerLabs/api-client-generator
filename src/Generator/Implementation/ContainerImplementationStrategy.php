<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\Container\PimpleContainer;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use UnexpectedValueException;

class ContainerImplementationStrategy implements ContainerImplementationInterface
{
    public const CONTAINER_PIMPLE          = 'pimple';
    public const CONTAINER_IMPLEMENTATIONS = [
        self::CONTAINER_PIMPLE => PimpleContainer::class,
    ];
    private ContainerImplementationInterface $containerImplementation;

    public function __construct(string $container, string $baseNamespace, CodeBuilder $builder)
    {
        if (!isset(self::CONTAINER_IMPLEMENTATIONS[$container])) {
            $supported = json_encode(self::CONTAINER_IMPLEMENTATIONS, JSON_THROW_ON_ERROR);

            throw new UnexpectedValueException(
                'Unsupported container `' . $container . '`. Should be one of ' . $supported
            );
        }
        $implementationClassName = self::CONTAINER_IMPLEMENTATIONS[$container];

        $this->containerImplementation = new $implementationClassName($baseNamespace, $builder);
    }

    public function generateInitContainerMethod(): MethodBuilder
    {
        return $this->containerImplementation->generateInitContainerMethod();
    }

    public function registerClosure(Variable $containerVariable, Expr $key, Closure $closure): Expr
    {
        return $this->containerImplementation->registerClosure($containerVariable, $key, $closure);
    }

    public function getClosure(Variable $containerVariable, Expr $key): Expr
    {
        return $this->containerImplementation->getClosure($containerVariable, $key);
    }

    public function getPackages(): array
    {
        return $this->containerImplementation->getPackages();
    }

    public function getContainerInitImports(): array
    {
        return $this->containerImplementation->getContainerInitImports();
    }

    public function getContainerRegisterImports(): array
    {
        return $this->containerImplementation->getContainerRegisterImports();
    }
}
