<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Builder;

use DoclerLabs\ApiClientGenerator\Output\Php\PhpVersionResolver;
use PhpParser\Builder;
use PhpParser\Builder\Method;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;

class MethodBuilder extends Method
{
    private PhpVersionResolver $versionResolver;

    public function __construct(string $name, PhpVersionResolver $versionResolver)
    {
        parent::__construct($name);
        $this->versionResolver = $versionResolver;
    }

    public function composeDocBlock(
        array $parameters = [],
        string $returnType = '',
        array $exceptions = [],
        bool $excludeFromCodeCoverage = false
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
        if ($excludeFromCodeCoverage) {
            $docLines[] = ' * @codeCoverageIgnore';
        }
        $docLines[] = '*/';

        $this->setDocComment(new Doc(implode("\n", $docLines)));

        return $this;
    }

    /**
     * @param Name|NullableType|string|null $type
     *
     * @return $this
     */
    public function setReturnType($type): self
    {
        if ($type === '' || $type === null) {
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
