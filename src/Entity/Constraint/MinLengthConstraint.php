<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class MinLengthConstraint implements ConstraintInterface
{
    private ?int $minLength = null;

    public function __construct(?int $minLength)
    {
        $this->minLength = $minLength;
    }

    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    public function exists(): bool
    {
        return $this->minLength !== null;
    }

    public function getIfCondition(Variable $variable, CodeBuilder $builder): Expr
    {
        return $builder->compare(
            $builder->funcCall('grapheme_strlen', [$variable]),
            '<',
            $builder->val($this->minLength)
        );
    }

    public function getExceptionMessage(): string
    {
        return sprintf('Length should be greater than %s.', $this->minLength);
    }
}
