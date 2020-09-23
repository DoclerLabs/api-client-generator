<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\Container\PimpleContainer;
use UnexpectedValueException;

class ContainerImplementationStrategy implements ContainerImplementationInterface
{
    public const CONTAINER_PIMPLE          = 'pimple';
    public const CONTAINER_IMPLEMENTATIONS = [
        self::CONTAINER_PIMPLE => PimpleContainer::class,
    ];
    private $containerImplementation;

    public function __construct(string $container, CodeBuilder $builder)
    {
        if (!isset(self::CONTAINER_IMPLEMENTATIONS[$container])) {
            $supported = json_encode(self::CONTAINER_IMPLEMENTATIONS, JSON_THROW_ON_ERROR);

            throw new UnexpectedValueException(
                'Unsupported container `' . $container . '`. Should be one of ' . $supported
            );
        }
        $implementationClassName = self::CONTAINER_IMPLEMENTATIONS[$container];

        $this->containerImplementation = new $implementationClassName($builder);
    }

    public function generateInitContainerMethod(): MethodBuilder
    {
        return $this->containerImplementation->generateInitContainerMethod();
    }

    public function getInitContainerImports(): array
    {
        return $this->containerImplementation->getInitContainerImports();
    }

    public function getPackages(): array
    {
        return $this->containerImplementation->getPackages();
    }
}
