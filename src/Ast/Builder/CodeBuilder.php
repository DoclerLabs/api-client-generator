<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast\Builder;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Entity\ImportCollection;
use InvalidArgumentException;
use PhpParser\BuilderFactory;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mod;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Name\Relative;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;
use UnexpectedValueException;

class CodeBuilder extends BuilderFactory
{
    public function __construct(private PhpVersion $phpVersion)
    {
    }

    public function method(string $name): MethodBuilder
    {
        return new MethodBuilder($name, $this->phpVersion);
    }

    public function closure(
        array $stmts,
        array $params,
        array $uses = [],
        string $returnType = '',
        bool $isStatic = true
    ): Closure {
        if (!empty($stmts)) {
            $subNodes['stmts'] = $stmts;
        }
        if (!empty($params)) {
            $subNodes['params'] = $params;
        }
        if (!empty($uses)) {
            $subNodes['uses'] = $uses;
        }
        if ($returnType !== '') {
            $subNodes['returnType'] = $returnType;
        }

        $subNodes['static'] = $isStatic;

        return new Closure($subNodes);
    }

    public function class(string $name): ClassBuilder
    {
        return new ClassBuilder($name);
    }

    public function array(array $items): Array_
    {
        $arrayItems = [];
        foreach ($items as $key => $value) {
            if (is_string($key)) {
                $arrayItems[] = new ArrayItem($value, $this->val($key));
            } else {
                $arrayItems[] = new ArrayItem($value);
            }
        }

        return new Array_($arrayItems);
    }

    public function getArrayItem(Expr $array, Expr $key): ArrayDimFetch
    {
        return new ArrayDimFetch($array, $key);
    }

    public function appendToArray(Variable $arrayVar, Expr $value): Expression
    {
        return new Expression($this->assign(new ArrayDimFetch($arrayVar), $value));
    }

    public function appendToAssociativeArray(Variable $arrayVar, Expr $key, Expr $value): Expression
    {
        return new Expression($this->assign($this->getArrayItem($arrayVar, $key), $value));
    }

    public function localProperty(
        string $name,
        string $type,
        string $docType,
        bool $nullable = false,
        Expr $default = null
    ): Property {
        $property = $this
            ->property($name)
            ->makePrivate();

        if (!empty($type) && $this->phpVersion->isPropertyTypeHintSupported()) {
            if ($nullable) {
                $property->setDefault(null);
                $type = '?' . $type;
            }

            $property->setType($type);
        } else {
            $docComment = sprintf('/** @var %s */', $docType);
            $property->setDocComment($docComment);
        }

        if ($default !== null) {
            $property->setDefault($default);
        }

        return $property->getNode();
    }

    public function localPropertyFetch(string $propertyName): PropertyFetch
    {
        return $this->propertyFetch($this->var('this'), $propertyName);
    }

    public function localNullsafePropertyFetch(string $propertyName): NullsafePropertyFetch
    {
        return new NullsafePropertyFetch($this->var('this'), BuilderHelpers::normalizeIdentifierOrExpr($propertyName));
    }

    public function localMethodCall(string $methodName, array $args = []): MethodCall
    {
        return $this->methodCall($this->var('this'), $methodName, $args);
    }

    public function nullsafeMethodCall(Expr $var, string $name, array $args = []): NullsafeMethodCall
    {
        return new NullsafeMethodCall(
            $var,
            BuilderHelpers::normalizeIdentifierOrExpr($name),
            $this->args($args)
        );
    }

    public function buildClass(string $namespace, ImportCollection $imports, Node $class): array
    {
        $nodes = [];

        if ($namespace === '') {
            throw new UnexpectedValueException('Namespace cannot be empty');
        }

        if ($namespace[0] === '\\') {
            $namespaceName = new FullyQualified(substr($namespace, 1));
        } elseif (0 === strpos($namespace, 'namespace\\')) {
            $namespaceName = new Relative(substr($namespace, strlen('namespace\\')));
        } else {
            $namespaceName = new Name($namespace);
        }

        $nodes[] = new Namespace_($namespaceName, $this->getStmts($imports->toArray(), $class));

        return $nodes;
    }

    public function return(Expr $expr = null, array $attributes = []): Return_
    {
        return new Return_($expr, $attributes);
    }

    public function castToObject(Expr $expr): Cast\Object_
    {
        return new Cast\Object_($expr);
    }

    public function castToArray(Expr $expr): Cast\Array_
    {
        return new Cast\Array_($expr);
    }

    public function expr(Expr $expr): Expression
    {
        return new Expression($expr);
    }

    public function assign(Expr $left, Expr $right): Assign
    {
        return new Assign($left, $right);
    }

    public function match(Expr $condition, MatchArm ...$cases): Match_
    {
        return new Match_($condition, $cases);
    }

    public function matchArm(array $conditions, Expr $body): MatchArm
    {
        return new MatchArm($conditions, $body);
    }

    public function switch(Expr $condition, Case_ ...$cases): Switch_
    {
        return new Switch_($condition, $cases);
    }

    public function case(Expr $condition, Stmt ...$stmts): Case_
    {
        return new Case_($condition, $stmts);
    }

    public function if(Expr $condition, array $stmts, array $elseIfs = [], Else_ $else = null): If_
    {
        $subNodes            = [];
        $subNodes['stmts']   = $stmts;
        $subNodes['elseifs'] = $elseIfs;
        $subNodes['else']    = $else;

        return new If_($condition, $subNodes);
    }

    public function elseIf(Expr $condition, array $stmts): ElseIf_
    {
        return new ElseIf_($condition, $stmts);
    }

    public function else(array $stmts): Else_
    {
        return new Else_($stmts);
    }

    public function ternary(Expr $condition, Expr $if, Expr $else): Ternary
    {
        return new Ternary($condition, $if, $else);
    }

    public function coalesce(Expr $left, Expr $right): Coalesce
    {
        return new Coalesce($left, $right);
    }

    public function instanceOf(Expr $left, Name $class): Instanceof_
    {
        return new Instanceof_($left, $class);
    }

    public function equals(Expr $left, Expr $right): Identical
    {
        return new Identical($left, $right);
    }

    public function notEquals(Expr $left, Expr $right): NotIdentical
    {
        return new NotIdentical($left, $right);
    }

    public function and(Expr $left, Expr $right): BooleanAnd
    {
        return new BooleanAnd($left, $right);
    }

    public function or(Expr $left, Expr $right): BooleanOr
    {
        return new BooleanOr($left, $right);
    }

    public function not(Expr $expr): BooleanNot
    {
        return new BooleanNot($expr);
    }

    public function throw(string $exceptionClassName, Expr $message = null): Stmt
    {
        $args = [];
        if ($message !== null) {
            $args = [$message];
        }

        return new Throw_($this->new($exceptionClassName, $args));
    }

    public function foreach(Expr $array, Variable $asValue, array $stmts, Variable $asKey = null): Foreach_
    {
        $subNodes = ['stmts' => $stmts];

        if ($asKey !== null) {
            $subNodes['keyVar'] = $asKey;
        }

        return new Foreach_($array, $asValue, $subNodes);
    }

    public function argument(Expr $argument, bool $byRef = false, bool $unpack = false): Arg
    {
        return new Arg($argument, $byRef, $unpack);
    }

    public function constant(string $name, Expr $value, int $visibility = Class_::MODIFIER_PUBLIC): ClassConst
    {
        if ($this->phpVersion->isClassConstantVisibilitySupported()) {
            return new ClassConst([new Const_($name, $value)], $visibility);
        }

        return new ClassConst([new Const_($name, $value)]);
    }

    public function tryCatch(array $tryStmts, array $catches, Finally_ $finally = null): TryCatch
    {
        return new TryCatch($tryStmts, $catches, $finally);
    }

    public function catch(array $types, Variable $variable, array $catchStmts): Catch_
    {
        return new Catch_($types, $variable, $catchStmts);
    }

    public function finally(array $finallyStmts): Finally_
    {
        return new Finally_($finallyStmts);
    }

    public function className(string $string): Name
    {
        return new Name($string);
    }

    public function compare(Expr $left, string $operator, Expr $right): BinaryOp
    {
        return match ($operator) {
            '>'     => new Greater($left, $right),
            '>='    => new GreaterOrEqual($left, $right),
            '<'     => new Smaller($left, $right),
            '<='    => new SmallerOrEqual($left, $right),
            default => throw new InvalidArgumentException('Unknown operator passed: ' . $operator),
        };
    }

    public function operation(Expr $left, string $operator, Expr $right): BinaryOp
    {
        return match ($operator) {
            '-'     => new Minus($left, $right),
            '+'     => new Plus($left, $right),
            '*'     => new Mul($left, $right),
            '/'     => new Div($left, $right),
            '%'     => new Mod($left, $right),
            default => throw new InvalidArgumentException('Unknown operator passed: ' . $operator),
        };
    }

    public function param(string $name): ParameterBuilder
    {
        return new ParameterBuilder($name, $this->phpVersion);
    }

    private function getStmts(array $imports, Node $class): array
    {
        $stmts = [];
        foreach ($imports as $import) {
            $stmts[] = $this->use($import)->getNode();
        }
        $stmts[] = $class;

        return $stmts;
    }
}
