<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast\Builder;

use DoclerLabs\ApiClientGenerator\Ast\ParameterNode;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use PhpParser\Builder\Param;
use PhpParser\Node\Expr\Variable;

class ParameterBuilder extends Param
{
    protected string   $docBlockType = '';
    private PhpVersion $versionResolver;

    public function __construct(string $name, PhpVersion $versionResolver)
    {
        parent::__construct($name);
        $this->versionResolver = $versionResolver;
    }

    public function setType($type, bool $isNullable = false): self
    {
        if ($isNullable) {
            if ($this->versionResolver->isNullableTypeHintSupported() && is_string($type)) {
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
