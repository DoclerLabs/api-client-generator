<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Ast\Visitor;

use DoclerLabs\ApiClientGenerator\Ast\Visitor\NamespaceSubstituteVisitor;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Ast\Visitor\NamespaceSubstituteVisitor
 */
class NamespaceSubstituteVisitorTest extends TestCase
{
    private const ORIGINAL_NAMESPACE   = 'Some\\Original\\Namespace';
    private const SUBSTITUTE_NAMESPACE = 'Substitute\\Namespace';
    private NamespaceSubstituteVisitor $sut;

    protected function setUp(): void
    {
        $this->sut = new NamespaceSubstituteVisitor(self::ORIGINAL_NAMESPACE, self::SUBSTITUTE_NAMESPACE);
    }

    public function testLeaveNodeNamespace(): void
    {
        $node = new Namespace_(new Name(self::ORIGINAL_NAMESPACE . '\\Subnamespace\\Class'));
        $this->sut->leaveNode($node);
        self::assertEquals($node->name, self::SUBSTITUTE_NAMESPACE . '\\Subnamespace\\Class');
    }

    public function testLeaveNodeUse(): void
    {
        $node = new Use_([new UseUse(new Name(self::ORIGINAL_NAMESPACE . '\\Subnamespace\\Class'))]);
        $this->sut->leaveNode($node);
        self::assertEquals($node->uses[0]->name, self::SUBSTITUTE_NAMESPACE . '\\Subnamespace\\Class');
    }
}
