<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast\Builder;

use DoclerLabs\ApiClientGenerator\Ast\ParameterNode;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use PhpParser\Builder\Param;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;

class ParameterBuilder extends Param
{
    protected string $docBlockType = '';

    protected int $flags = 0;

    public function __construct(string $name, private PhpVersion $phpVersion)
    {
        parent::__construct($name);
    }

    public function setType($type, bool $isNullable = false): self
    {
        if (empty($type)) {
            return $this;
        }

        if ($isNullable) {
            if ($this->phpVersion->isNullableTypeHintSupported() && is_string($type)) {
                return parent::setType(sprintf('?%s', $type));
            }

            return $this;
        }

        return parent::setType($type);
    }

    public function getNode(): ParameterNode
    {
        return new ParameterNode(
            new Variable($this->name),
            $this->default,
            $this->type,
            $this->flags,
            $this->byRef,
            $this->variadic,
            [],
            $this->docBlockType
        );
    }

    public function setDocBlockType(string $docBlockType): self
    {
        $this->docBlockType = $docBlockType;

        return $this;
    }

    public function makePrivate(): self {
        $this->flags += Class_::MODIFIER_PRIVATE;
        return $this;
    }
}
