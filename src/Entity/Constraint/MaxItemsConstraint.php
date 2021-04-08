<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class MaxItemsConstraint implements ConstraintInterface
{
    private ?int $maxItems = null;

    public function __construct(?int $maxItems)
    {
        $this->maxItems = $maxItems;
    }

    public function getMaxItems(): ?int
    {
        return $this->maxItems;
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
