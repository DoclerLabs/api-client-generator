<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast\Builder;

use DoclerLabs\ApiClientGenerator\Ast\ParameterNode;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use PhpParser\Builder\Param;
use PhpParser\Node\Expr\Variable;

class ParameterBuilder extends Param
{
    protected string $docBlockType = '';

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
                // mixed already includes null and cannot be marked nullable
                return parent::setType($type === FieldType::PHP_TYPE_MIXED ? $type : sprintf('?%s', $type));
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
}
