<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation;

use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;

interface ContainerImplementationInterface
{
    public function generateInitContainerMethod(): MethodBuilder;

    public function registerClosure(Variable $containerVariable, Expr $key, Closure $closure): Expr;

    public function getClosure(Variable $containerVariable, Expr $key): Expr;

    public function getPackages(): array;

    public function getContainerInitImports(): array;

    public function getContainerRegisterImports(): array;
}
