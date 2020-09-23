<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation;

use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;

interface ContainerImplementationInterface
{
    public function generateInitContainerMethod(): MethodBuilder;

    public function getInitContainerImports(): array;

    public function getPackages(): array;
}