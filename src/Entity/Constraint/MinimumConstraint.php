<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class MinimumConstraint implements ConstraintInterface
{
    private ?int  $minimum          = null;
    private ?bool $exclusiveMinimum = null;

    public function __construct(?int $minimum, ?bool $exclusiveMinimum)
    {
        $this->minimum          = $minimum;
        $this->exclusiveMinimum = $exclusiveMinimum;
    }

    public function exists(): bool
    {
        return $this->minimum !== null;
    }

    public function getIfCondition(Variable $variable, CodeBuilder $builder): Expr
    {
        return $builder->compare(
            $variable,
            $this->exclusiveMinimum === true ? '<=' : '<',
            $builder->val($this->minimum)
        );
    }

    public function getExceptionMessage(): string
    {
        return sprintf(
            'Cannot be less than %s%s.',
            $this->exclusiveMinimum === true ? 'or equal to ' : '',
            $this->minimum
        );
    }
}
