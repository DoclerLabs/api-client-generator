<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class MinimumConstraint implements ConstraintInterface
{
    private ?float $minimum = null;

    private ?bool $exclusiveMinimum = null;

    private FieldType $fieldType;

    public function __construct(?float $minimum, ?bool $exclusiveMinimum, FieldType $fieldType)
    {
        $this->minimum          = $minimum;
        $this->exclusiveMinimum = $exclusiveMinimum;
        $this->fieldType        = $fieldType;
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
            $builder->val($this->fieldType->isInteger() ? (int)$this->minimum : $this->minimum)
        );
    }

    public function getExceptionMessage(): string
    {
        return sprintf(
            'Cannot be less than %s%s.',
            $this->exclusiveMinimum === true ? 'or equal to ' : '',
            $this->fieldType->isInteger() ? (int)$this->minimum : $this->minimum
        );
    }
}
