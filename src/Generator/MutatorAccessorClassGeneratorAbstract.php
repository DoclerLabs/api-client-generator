<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DateTimeInterface;
use DoclerLabs\ApiClientException\RequestValidationException;
use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\ConstraintInterface;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\SchemaNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

abstract class MutatorAccessorClassGeneratorAbstract extends GeneratorAbstract
{
    protected CodeBuilder $builder;

    abstract public function generate(Specification $specification, PhpFileCollection $fileRegistry): void;

    protected function generateValidationStmts(Field $field): array
    {
        return array_filter([
            $this->generateEnumValidation($field),
            ...$this->generateConstraints($field)
        ]);
    }

    protected function generateSet(Field $field): ClassMethod
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
        $phpDocType = $field->getPhpDocType();

        $return = $this->builder->return($this->builder->localPropertyFetch($field->getPhpVariableName()));

        return $this->builder
            ->method($this->getGetMethodName($field))
            ->makePublic()
            ->addStmt($return)
            ->setReturnType($field->getPhpTypeHint(), $field->isNullable() || !$field->isRequired())
            ->composeDocBlock([], $phpDocType)
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

    protected function generateEnumStatements(Field $field): array
    {
        $statements = [];
        $enumValues = $field->getEnumValues();
        if (!empty($enumValues)) {
            $enumConstCalls = [];
            foreach ($enumValues as $enumValue) {
                $constName    = SchemaNaming::getEnumConstName($field, $enumValue);
                $statements[] = $this->builder->constant(
                    $constName,
                    $this->builder->val($enumValue)
                );

                $enumConstCalls[] = $this->builder->classConstFetch('self', $constName);
            }
            $statements[] = $this->builder->constant(
                SchemaNaming::getAllowedEnumConstName($field),
                $this->builder->array($enumConstCalls)
            );
        }

        return $statements;
    }

    protected function generateEnumValidation(Field $root): ?Stmt
    {
        $enumValues = $root->getEnumValues();
        if (empty($enumValues)) {
            return null;
        }

        $this
            ->addImport(RequestValidationException::class);

        $propertyVar       = $this->builder->var($root->getPhpVariableName());
        $allowedConstFetch = $this->builder->classConstFetch(
            'self',
            SchemaNaming::getAllowedEnumConstName($root)
        );

        $inArrayArgs = [
            $propertyVar,
            $allowedConstFetch,
            $this->builder->val(true),
        ];
        $ifCondition = $this->builder->not($this->builder->funcCall('in_array', $inArrayArgs));

        $exceptionMessage = $this->builder->funcCall(
            'sprintf',
            [
                'Invalid %s value. Given: `%s` Allowed: %s',
                $root->getName(),
                $propertyVar,
                $this->builder->funcCall('json_encode', [$allowedConstFetch]),
            ]
        );

        $ifStmt = $this->builder->throw('RequestValidationException', $exceptionMessage);

        return $this->builder->if($ifCondition, [$ifStmt]);
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

            $stmts[] = $this->builder->if(
                $constraint->getIfCondition($propertyVar, $this->builder),
                [
                    $this->builder->throw('RequestValidationException', $exceptionMessage)
                ]
            );
        }

        if (!empty($stmts)) {
            $this->addImport(RequestValidationException::class);
        }

        return $stmts;
    }
}
