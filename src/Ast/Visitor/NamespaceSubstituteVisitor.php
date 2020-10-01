<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
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
        if ($node instanceof Namespace_ && $node->name !== null) {
            $newNamespace = str_replace($this->original, $this->substitute, $node->name->toString());
            $node->name = new Name($newNamespace);
        }
        if ($node instanceof Use_) {
            $use = $node->uses[0];
            if ($use->name !== null) {
                $newUse = str_replace($this->original, $this->substitute, $use->name->toString());
                $use->name = new Name($newUse);
            }
        }

        return null;
    }
}