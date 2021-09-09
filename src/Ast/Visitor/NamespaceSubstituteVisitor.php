<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeVisitorAbstract;

class NamespaceSubstituteVisitor extends NodeVisitorAbstract
{
    private string $original;
    private string $substitute;

    public function __construct(string $original, string $substitute)
    {
        $this->original = $original;
        $this->substitute = $substitute;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->renameNode($node);
        } elseif ($node instanceof Use_) {
            $this->renameNode($node->uses[0]);
        }

        return null;
    }

    /**
     * @param Namespace_|UseUse $namespacedStatement
     */
    private function renameNode($namespacedStatement): void
    {
        if ($namespacedStatement->name !== null) {
            $newName = str_replace($this->original, $this->substitute, $namespacedStatement->name->toString());
            $namespacedStatement->name = new Name($newName);
        }
    }
}
