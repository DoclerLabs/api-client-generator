<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation;

use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;

interface HttpClientImplementationInterface
{
    public function generateInitBaseClientMethod(): MethodBuilder;

    public function getInitBaseClientImports(): array;

    public function getPackages(): array;
}