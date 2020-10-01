<?php declare(strict_types=1);

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
    private PhpVersion $versionResolver;

    public function __construct(string $name, PhpVersion $versionResolver)
    {
        parent::__construct($name);
        $this->versionResolver = $versionResolver;
    }

    public function composeDocBlock(
        array $parameters = [],
        string $returnType = '',
        array $exceptions = []
    ): self {
        if (empty($parameters) && $returnType === '' && empty($exceptions)) {
            return $this;
        }

        $docLines[] = '/**';
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
        if ($type === 'mixed') {
            return $this;
        }

        if ($type === null) {
            if ($this->versionResolver->isVoidReturnTypeSupported()) {
                return parent::setReturnType('void');
            }

            return $this;
        }

        if ($isNullable) {
            if ($this->versionResolver->isNullableTypeHintSupported() && is_string($type)) {
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
