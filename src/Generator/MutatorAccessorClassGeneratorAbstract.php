<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DateTimeInterface;
use DoclerLabs\ApiClientException\RequestValidationException;
use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\ConstraintInterface;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\SchemaNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeAbstract;

abstract class MutatorAccessorClassGeneratorAbstract extends GeneratorAbstract
{
    protected CodeBuilder $builder;

    abstract public function generate(Specification $specification, PhpFileCollection $fileRegistry): void;

    protected function generateValidationStmts(Field $field): array
    {
        return array_filter(
            [
                ...$this->generateConstraints($field),
            ]
        );
    }

    /**
     * @param array<int, NodeAbstract> $additionalStatements
     */
    protected function generateSet(Field $field, array $additionalStatements = []): ClassMethod
    {
        $statements         = $this->generateValidationStmts($field);
        $thrownExceptionMap = empty($statements) ? [] : ['RequestValidationException' => true];
        $docType            = $field->getPhpDocType($field->isNullable());
        $param              = $this->builder
            ->param($field->getPhpVariableName())
            ->setType($field->getPhpTypeHint(), $field->isNullable())
            ->setDocBlockType($docType)
            ->getNode();

        $statements[] = $this->builder->assign(
            $this->builder->localPropertyFetch($field->getPhpVariableName()),
            $this->builder->var($field->getPhpVariableName())
        );

        $statements = array_merge($statements, $additionalStatements);

        $return     = $this->builder->return($this->builder->var('this'));
        $returnType = 'self';

        return $this->builder
            ->method($this->getSetMethodName($field))
            ->makePublic()
            ->addParam($param)
            ->addStmts($statements)
            ->addStmt($return)
            ->setReturnType($returnType)
            ->composeDocBlock([$param], $returnType, array_keys($thrownExceptionMap))
            ->getNode();
    }

    protected function generateGet(Field $field): ClassMethod
    {
        $return = $this->builder->return($this->builder->localPropertyFetch($field->getPhpVariableName()));

        return $this->builder
            ->method($this->getGetMethodName($field))
            ->makePublic()
            ->addStmt($return)
            ->setReturnType($field->getPhpTypeHint(), $field->isNullable() || !$field->isRequired())
            ->composeDocBlock([], $field->getPhpDocType())
            ->getNode();
    }

    protected function generateProperty(Field $field): Property
    {
        if ($field->isDate()) {
            $this->addImport(DateTimeInterface::class);
        }

        return $this->builder->localProperty(
            $field->getPhpVariableName(),
            $field->getPhpTypeHint(),
            $field->getPhpDocType(),
            $field->isOptional() || $field->isNullable()
        );
    }

    protected function getSetMethodName(Field $field): string
    {
        return sprintf('set%s', ucfirst($field->getPhpVariableName()));
    }

    protected function getGetMethodName(Field $field): string
    {
        return sprintf('get%s', ucfirst($field->getPhpVariableName()));
    }

    protected function getHasMethodName(Field $field): string
    {
        return sprintf('has%s', ucfirst($field->getPhpVariableName()));
    }

    protected function generateEnumStatements(Field $field): array
    {
        $statements = [];
        $enumValues = $field->getEnumValues();
        if (!empty($enumValues)) {
            foreach ($enumValues as $enumValue) {
                if (is_string($enumValue)) {
                    $constName    = SchemaNaming::getEnumConstName($field, $enumValue);
                    $statements[] = $this->builder->constant(
                        $constName,
                        $this->builder->val($enumValue)
                    );
                }
            }
        }

        return $statements;
    }

    protected function generateConstraints(Field $root): array
    {
        $stmts = [];

        /** @var ConstraintInterface $constraint */
        foreach ($root->getConstraints() as $constraint) {
            if (!$constraint->exists()) {
                continue;
            }

            $propertyVar      = $this->builder->var($root->getPhpVariableName());
            $exceptionMessage = $this->builder->funcCall(
                'sprintf',
                [
                    'Invalid %s value. Given: `%s`. ' . $constraint->getExceptionMessage(),
                    $root->getName(),
                    $propertyVar,
                ]
            );

            $ifConditionExpr = $constraint->getIfCondition($propertyVar, $this->builder);

            if ($root->isNullable()) {
                $ifConditionExpr = $this->builder->and(
                    $this->builder->notEquals($propertyVar, $this->builder->val(null)),
                    $ifConditionExpr
                );
            }

            $stmts[] = $this->builder->if(
                $ifConditionExpr,
                [
                    $this->builder->throw('RequestValidationException', $exceptionMessage),
                ]
            );
        }

        if (!empty($stmts)) {
            $this->addImport(RequestValidationException::class);
        }

        return $stmts;
    }
}
