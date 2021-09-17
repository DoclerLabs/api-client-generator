<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Ast\Builder;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Entity\ImportCollection;
use Exception;
use InvalidArgumentException;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder
 */
class CodeBuilderTest extends TestCase
{
    private CodeBuilder $sut;
    /** @var PhpVersion|MockObject */
    private $phpVersionResolver;
    private Standard $printer;

    protected function setUp(): void
    {
        $this->phpVersionResolver = $this->createMock(PhpVersion::class);
        $this->sut                = new CodeBuilder($this->phpVersionResolver);
        $this->printer            = new Standard();
    }

    public function testArray(): void
    {
        $items = [
            $this->sut->val(1),
            $this->sut->val(2),
            $this->sut->val(3),
        ];
        $array = $this->sut->array($items);
        self::assertEquals('array(1, 2, 3)', $this->printer->prettyPrintExpr($array));
    }

    public function testAppendToArray(): void
    {
        $arrayVar      = $this->sut->var('someArr');
        $appendToArray = $this->sut->appendToArray($arrayVar, $this->sut->val(4));
        self::assertEquals('$someArr[] = 4', $this->printer->prettyPrintExpr($appendToArray->expr));
    }

    public function testAppendToAssociativeArray(): void
    {
        $arrayVar      = $this->sut->var('someArr');
        $appendToArray = $this->sut->appendToAssociativeArray($arrayVar, $this->sut->val('key'), $this->sut->val(5));
        self::assertEquals('$someArr[\'key\'] = 5', $this->printer->prettyPrintExpr($appendToArray->expr));
    }

    public function testArgument(): void
    {
        $itemsVar = $this->sut->var('items');
        $newClass = $this->sut->new(
            'newClass',
            [$this->sut->argument($itemsVar, false, true)]
        );
        self::assertEquals('new newClass(...$items)', $this->printer->prettyPrintExpr($newClass));
    }

    public function testNot(): void
    {
        $not = $this->sut->not($this->sut->val(true));
        self::assertEquals('!true', $this->printer->prettyPrintExpr($not));
    }

    public function testTryCatch(): void
    {
        $tryStmts[]     = $this->sut->expr($this->sut->assign($this->sut->val(1), $this->sut->val(1)));
        $catchStmts[]   = $this->sut->expr($this->sut->assign($this->sut->val(2), $this->sut->val(2)));
        $exceptionVar   = $this->sut->var('exception');
        $catches[]      = $this->sut->catch(
            [
                $this->sut->className('LogicException'),
                $this->sut->className('RuntimeException'),
            ],
            $exceptionVar,
            $catchStmts
        );
        $catches[]      = $this->sut->catch([$this->sut->className('Throwable')], $exceptionVar, []);
        $finallyStmts[] = $this->sut->expr($this->sut->assign($this->sut->val(3), $this->sut->val(3)));
        $finally        = $this->sut->finally($finallyStmts);
        $tryCatch       = $this->sut->tryCatch($tryStmts, $catches, $finally);
        self::assertEquals(
            <<<'EOD'
try {
    1 = 1;
} catch (LogicException|RuntimeException $exception) {
    2 = 2;
} catch (Throwable $exception) {
} finally {
    3 = 3;
}
EOD,
            $this->printer->prettyPrint([$tryCatch])
        );
    }

    public function testCoalesce(): void
    {
        $coalesce = $this->sut->coalesce($this->sut->var('var'), $this->sut->val(true));
        self::assertEquals('$var ?? true', $this->printer->prettyPrintExpr($coalesce));
    }

    public function testGetArrayItem(): void
    {
        $getArrayItem = $this->sut->getArrayItem($this->sut->array([]), $this->sut->val('some'));
        self::assertEquals('array()[\'some\']', $this->printer->prettyPrintExpr($getArrayItem));
    }

    public function testAnd(): void
    {
        $and = $this->sut->and($this->sut->val(true), $this->sut->val(false));
        self::assertEquals('true && false', $this->printer->prettyPrintExpr($and));
    }

    public function testLocalPropertyFetch(): void
    {
        $localPropertyFetch = $this->sut->localPropertyFetch('local');
        self::assertEquals('$this->local', $this->printer->prettyPrintExpr($localPropertyFetch));
    }

    public function testIf(): void
    {
        $ifStmts[] = $this->sut->expr($this->sut->assign($this->sut->val(1), $this->sut->val(1)));
        $stmts[]   = $this->sut->if($this->sut->val(true), $ifStmts);

        $elseIfStmts[] = $this->sut->expr($this->sut->assign($this->sut->val(2), $this->sut->val(2)));
        $stmts[]       = $this->sut->elseIf($this->sut->val(false), $elseIfStmts);

        $elseStmts[] = $this->sut->expr($this->sut->assign($this->sut->val(2), $this->sut->val(2)));
        $stmts[]     = $this->sut->else($elseStmts);
        self::assertEquals(
            <<<'EOD'
if (true) {
    1 = 1;
}
elseif (false) {
    2 = 2;
}
else {
    2 = 2;
}
EOD,
            $this->printer->prettyPrint($stmts)
        );
    }

    public function testLocalMethodCall(): void
    {
        $localPropertyFetch = $this->sut->localMethodCall('local');
        self::assertEquals('$this->local()', $this->printer->prettyPrintExpr($localPropertyFetch));
    }

    public function testOperation(): void
    {
        $left  = $this->sut->var('left');
        $right = $this->sut->var('right');

        $op = $this->sut->operation($left, '+', $right);
        self::assertEquals('$left + $right', $this->printer->prettyPrintExpr($op));

        $op = $this->sut->operation($left, '-', $right);
        self::assertEquals('$left - $right', $this->printer->prettyPrintExpr($op));

        $op = $this->sut->operation($left, '*', $right);
        self::assertEquals('$left * $right', $this->printer->prettyPrintExpr($op));

        $op = $this->sut->operation($left, '/', $right);
        self::assertEquals('$left / $right', $this->printer->prettyPrintExpr($op));

        $op = $this->sut->operation($left, '%', $right);
        self::assertEquals('$left % $right', $this->printer->prettyPrintExpr($op));

        $this->expectException(InvalidArgumentException::class);
        $this->sut->operation($left, 'unknown', $right);
    }

    public function testClass(): void
    {
        $method   = $this->sut->method('testify');
        $property = $this->sut->localProperty('some', 'string', 'string');
        $class    = $this->sut->class('Test')
            ->addStmt($property)
            ->addStmt($method)
            ->getNode();
        self::assertEquals(
            <<<'EOD'
class Test
{
    /** @var string */
    private $some;
    function testify()
    {
    }
}
EOD,
            $this->printer->prettyPrint([$class])
        );
    }

    public function testOr(): void
    {
        $left  = $this->sut->var('left');
        $right = $this->sut->var('right');

        $or = $this->sut->or($left, $right);
        self::assertEquals('$left || $right', $this->printer->prettyPrintExpr($or));
    }

    public function testEquals(): void
    {
        $left  = $this->sut->var('left');
        $right = $this->sut->var('right');

        $equals = $this->sut->equals($left, $right);
        self::assertEquals('$left === $right', $this->printer->prettyPrintExpr($equals));
    }

    public function testInstanceOf(): void
    {
        $left = $this->sut->var('left');

        $instanceOf = $this->sut->instanceOf($left, $this->sut->className('Exception'));
        self::assertEquals('$left instanceof Exception', $this->printer->prettyPrintExpr($instanceOf));
    }

    public function testReturn(): void
    {
        $left = $this->sut->var('left');

        $return = $this->sut->return($left);
        self::assertEquals('return $left;', $this->printer->prettyPrint([$return]));
    }

    public function testForeach(): void
    {
        $arr            = $this->sut->var('array');
        $as             = $this->sut->var('item');
        $foreachStmts[] = $this->sut->expr($this->sut->assign($this->sut->val(1), $this->sut->val(1)));

        $foreach = $this->sut->foreach($arr, $as, $foreachStmts);
        self::assertEquals(
            <<<'EOD'
foreach ($array as $item) {
    1 = 1;
}
EOD,
            $this->printer->prettyPrint([$foreach])
        );
    }

    public function testBuildClass(): void
    {
        $class = $this->sut->buildClass(
            'Name\\Space',
            (new ImportCollection())->add(Exception::class),
            $this->sut->class('Test')->getNode()
        );
        self::assertEquals(
            <<<'EOD'
namespace Name\Space;

use Exception;
class Test
{
}
EOD,
            $this->printer->prettyPrint($class)
        );
    }

    public function testTernary(): void
    {
        $left = $this->sut->var('left');

        $ternary = $this->sut->ternary($this->sut->val(true), $left, $this->sut->val(null));
        self::assertEquals('true ? $left : null', $this->printer->prettyPrint([$ternary]));
    }

    public function testNotEquals(): void
    {
        $left  = $this->sut->var('left');
        $right = $this->sut->var('right');

        $notEquals = $this->sut->notEquals($left, $right);
        self::assertEquals('$left !== $right', $this->printer->prettyPrintExpr($notEquals));
    }
}
