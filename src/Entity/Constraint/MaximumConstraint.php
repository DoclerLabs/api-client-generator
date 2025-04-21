<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class MaximumConstraint implements ConstraintInterface
{
    public function __construct(private ?float $maximum, private bool|float|null $exclusiveMaximum, private FieldType $fieldType)
    {
        // openapi 3.1 exclusiveMaximum is no longer a boolean
        if (is_float($exclusiveMaximum)) {
            $this->maximum          = $exclusiveMaximum;
            $this->exclusiveMaximum = true;
        }
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
            $builder->val($this->fieldType->isInteger() ? (int)$this->maximum : $this->maximum)
        );
    }

    public function getExceptionMessage(): string
    {
        return sprintf(
            'Cannot be greater than %s%s.',
            $this->exclusiveMaximum === true ? 'or equal to ' : '',
            $this->fieldType->isInteger() ? (int)$this->maximum : $this->maximum
        );
    }
}
