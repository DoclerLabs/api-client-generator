<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class MaxLengthConstraint implements ConstraintInterface
{
    private ?int $maxLength = null;

    public function __construct(?int $maxLength)
    {
        $this->maxLength = $maxLength;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function exists(): bool
    {
        return $this->maxLength !== null;
    }

    public function getIfCondition(Variable $variable, CodeBuilder $builder): Expr
    {
        return $builder->compare(
            $builder->funcCall('grapheme_strlen', [$variable]),
            '>',
            $builder->val($this->maxLength)
        );
    }

    public function getExceptionMessage(): string
    {
        return sprintf('Length should be less than %s.', $this->maxLength);
    }
}
