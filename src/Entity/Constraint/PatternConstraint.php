<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;

class PatternConstraint implements ConstraintInterface
{
    private ?string $pattern;

    public function __construct(?string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function exists(): bool
    {
        return $this->pattern !== null;
    }

    public function getIfCondition(Variable $variable, CodeBuilder $builder): Expr
    {
        return $builder->notEquals(
            $builder->funcCall('preg_match', [$builder->val('/' . $this->pattern . '/'), $variable]),
            $builder->val(1)
        );
    }

    public function getExceptionMessage(): string
    {
        return sprintf('Pattern is %s.', $this->pattern);
    }
}
