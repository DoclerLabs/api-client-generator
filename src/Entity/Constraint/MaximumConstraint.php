<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class MaximumConstraint implements ConstraintInterface
{
    private ?float $maximum = null;

    private ?bool $exclusiveMaximum = null;

    private FieldType $fieldType;

    public function __construct(?float $maximum, ?bool $exclusiveMaximum, FieldType $fieldType)
    {
        $this->maximum          = $maximum;
        $this->exclusiveMaximum = $exclusiveMaximum;
        $this->fieldType        = $fieldType;
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
