<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Builder;

use PhpParser\Builder\Param;
use PhpParser\Node;

class Parameter extends Param
{
    protected string $docBlockType = '';

    public function setType($type): self
    {
        if ($type !== '') {
            return parent::setType($type);
        }

        return $this;
    }

    public function getNode(): ParameterNode
    {
        return new ParameterNode(
            new Node\Expr\Variable($this->name),
            $this->default,
            $this->type,
            $this->byRef,
            $this->variadic,
            [],
            $this->docBlockType
        );
    }

    public function setDocBlockType(string $docBlockType): Parameter
    {
        $this->docBlockType = $docBlockType;

        return $this;
    }
}
