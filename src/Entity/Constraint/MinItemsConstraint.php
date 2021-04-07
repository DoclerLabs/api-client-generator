<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class MinItemsConstraint implements ConstraintInterface
{
    private ?int $minItems = null;

    public function __construct(?int $minItems)
    {
        $this->minItems = $minItems;
    }

    public function getMinItems(): ?int
    {
        return $this->minItems;
    }

    public function exists(): bool
    {
        return $this->minItems !== null;
    }

    public function getIfCondition(Variable $variable, CodeBuilder $builder): Expr
    {
        return $builder->compare(
            $builder->funcCall('count', [$variable]),
            '<',
            $builder->val($this->minItems)
        );
    }

    public function getExceptionMessage(): string
    {
        return sprintf('Expected min items: `%s`.', $this->minItems);
    }
}
