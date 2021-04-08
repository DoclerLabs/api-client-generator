<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class MaximumConstraint implements ConstraintInterface
{
    private ?int  $maximum          = null;
    private ?bool $exclusiveMaximum = null;

    public function __construct(?int $maximum, ?bool $exclusiveMaximum)
    {
        $this->maximum          = $maximum;
        $this->exclusiveMaximum = $exclusiveMaximum;
    }

    public function getMaximum(): ?int
    {
        return $this->maximum;
    }

    public function isExclusiveMaximum(): ?bool
    {
        return $this->exclusiveMaximum;
    }

    public function exists(): bool
    {
        return $this->maximum !== null;
    }

    public function getIfCondition(Variable $variable, CodeBuilder $builder): Expr
    {
        return $builder->compare(
            $variable,
            $this->exclusiveMaximum === true ? '>=' : '>',
            $builder->val($this->maximum)
        );
    }

    public function getExceptionMessage(): string
    {
        return sprintf(
            'Cannot be greater than %s%s.',
            $this->exclusiveMaximum === true ? 'or equal to ' : '',
            $this->maximum
        );
    }
}
