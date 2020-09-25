<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation;

use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;

interface ContainerImplementationInterface
{
    public function generateInitContainerMethod(): MethodBuilder;

    public function generateRegisterMethod(array $compositeFields): MethodBuilder;

    public function getPackages(): array;

    public function getContainerInitImports(): array;

    public function getContainerRegisterImports(): array;
}