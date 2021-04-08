<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

interface ConstraintInterface
{
    public function exists(): bool;

    public function getIfCondition(Variable $variable, CodeBuilder $builder): Expr;

    public function getExceptionMessage(): string;
}
