<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast\Builder;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use PhpParser\Builder;
use PhpParser\Builder\Method;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;

class MethodBuilder extends Method
{
    public const RETURN_TYPE_VOID = 'void';

    private PhpVersion $phpVersion;

    public function __construct(string $name, PhpVersion $phpVersion)
    {
        parent::__construct($name);
        $this->phpVersion = $phpVersion;
    }

    public function composeDocBlock(
        array $parameters = [],
        string $returnType = '',
        array $exceptions = [],
        array $description = []
    ): self {
        if (empty($parameters) && $returnType === '' && empty($exceptions) && empty($description)) {
            return $this;
        }

        $docLines[] = '/**';
        foreach ($description as $line) {
            $docLines[] = sprintf(' *%s%s', $line ? ' ' : '', $line);
        }
        foreach ($parameters as $parameter) {
            $dockBlock  = $parameter->getDocBlockType() !== '' ? $parameter->getDocBlockType() : $parameter->type;
            $docLines[] = sprintf(' * @param %s $%s', $dockBlock, $parameter->var->name);
        }
        if ($returnType !== '') {
            $docLines[] = sprintf(' * @return %s', $returnType);
        }
        foreach ($exceptions as $exception) {
            $docLines[] = sprintf(' * @throws %s', $exception);
        }
        $docLines[] = '*/';

        $this->setDocComment(new Doc(implode("\n", $docLines)));

        return $this;
    }

    /**
     * @param Name|NullableType|string|null $type
     * @param bool                          $isNullable
     *
     * @return $this
     */
    public function setReturnType($type, $isNullable = false): self
    {
        if (empty($type)) {
            return $this;
        }

        if ($type === self::RETURN_TYPE_VOID) {
            if ($this->phpVersion->isVoidReturnTypeSupported()) {
                return parent::setReturnType($type);
            }

            return $this;
        }

        if ($isNullable) {
            if ($this->phpVersion->isNullableTypeHintSupported() && is_string($type)) {
                return parent::setReturnType(sprintf('?%s', $type));
            }

            return $this;
        }

        return parent::setReturnType($type);
    }

    /**
     * @param Builder|Node|null $stmt
     *
     * @return $this
     */
    public function addStmt($stmt): self
    {
        if ($stmt === null) {
            return $this;
        }

        return parent::addStmt($stmt);
    }
}
