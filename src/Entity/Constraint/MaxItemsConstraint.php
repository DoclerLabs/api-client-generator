<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class MaxItemsConstraint implements ConstraintInterface
{
    public function __construct(private ?int $maxItems)
    {
    }

    public function exists(): bool
    {
        return $this->maxItems !== null;
    }

    public function getIfCondition(Variable $variable, CodeBuilder $builder): Expr
    {
        return $builder->compare(
            $builder->funcCall('count', [$variable]),
            '>',
            $builder->val($this->maxItems)
        );
    }

    public function getExceptionMessage(): string
    {
        return sprintf('Expected max items: `%s`.', $this->maxItems);
    }
}
