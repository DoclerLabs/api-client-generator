<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast;

use PhpParser\Node\Expr;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\UnionType;

class ParameterNode extends Param
{
    public function __construct(
        $var,
        Expr $default = null,
        $type = null,
        int $flags = 0,
        bool $byRef = false,
        bool $variadic = false,
        array $attributes = [],
        protected string $docBlockType = ''
    ) {
        parent::__construct($var, $default, $type, $byRef, $variadic, $attributes, $flags);
    }

    public function getDocBlockType(): string
    {
        if ($this->docBlockType !== '') {
            return $this->docBlockType;
        }

        if ($this->type instanceof UnionType) {
            return implode("|", $this->type->getAttribute('types'));
        }

        if ($this->type instanceof NullableType) {
            return sprintf('%s|null', $this->type->type->toString());
        }

        if ($this->type !== null) {
            return $this->type->toString();
        }

        return '';
    }
}
