<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Builder;

use PhpParser\Node\Expr;
use PhpParser\Node\Param;

class ParameterNode extends Param
{
    protected string $docBlockType;

    public function __construct(
        $var,
        Expr $default = null,
        $type = null,
        bool $byRef = false,
        bool $variadic = false,
        array $attributes = [],
        string $docBlockType = ''
    ) {
        $this->docBlockType = $docBlockType;

        parent::__construct($var, $default, $type, $byRef, $variadic, $attributes);
    }

    public function getDocBlockType(): string
    {
        if ($this->docBlockType === '' && $this->type !== null) {
            return (string)$this->type;
        }

        return $this->docBlockType;
    }
}
