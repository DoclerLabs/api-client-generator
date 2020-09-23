<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation;

use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;

interface HttpMessageImplementationInterface
{
    public function generateInitRequestMapperMethod(): MethodBuilder;

    public function generateRequestMapMethod(): MethodBuilder;

    public function getRequestMapperClassName(): string;

    public function getPackages(): array;
}