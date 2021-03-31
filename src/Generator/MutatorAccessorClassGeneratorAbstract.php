<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DateTimeInterface;
use DoclerLabs\ApiClientException\RequestValidationException;
use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
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
            $this->generateMinimumValidation($field),
            $this->generateMaximumValidation($field),
            $this->generateMinLengthValidation($field),
            $this->generateMaxLengthValidation($field),
            $this->generatePatternValidation($field),
            $this->generateMinItemsValidation($field),
            $this->generateMaxItemsValidation($field),
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

    protected function generateMinimumValidation(Field $root): ?Stmt
    {
        $minimum = $root->getMinimum();
        if ($minimum === null) {
            return null;
        }

        $this->addImport(RequestValidationException::class);

        $propertyVar = $this->builder->var($root->getPhpVariableName());
        $operator    = $root->isExclusiveMinimum() === true ? '<=' : '<';

        $ifCondition = $this->builder->compare(
            $propertyVar,
            $operator,
            $this->builder->val($minimum)
        );

        $exceptionMessage = $this->builder->funcCall(
            'sprintf',
            [
                'Invalid %s value. Given: `%s`, cannot be %s than %s',
                $root->getName(),
                $propertyVar,
                $operator,
                $minimum,
            ]
        );

        $ifStmt = $this->builder->throw('RequestValidationException', $exceptionMessage);

        return $this->builder->if($ifCondition, [$ifStmt]);
    }

    protected function generateMaximumValidation(Field $root): ?Stmt
    {
        $maximum = $root->getMaximum();
        if ($maximum === null) {
            return null;
        }

        $this->addImport(RequestValidationException::class);

        $propertyVar = $this->builder->var($root->getPhpVariableName());
        $operator    = $root->isExclusiveMaximum() === true ? '>=' : '>';

        $ifCondition = $this->builder->compare(
            $propertyVar,
            $operator,
            $this->builder->val($maximum)
        );

        $exceptionMessage = $this->builder->funcCall(
            'sprintf',
            [
                'Invalid %s value. Given: `%s`, cannot be %s than %s',
                $root->getName(),
                $propertyVar,
                $operator,
                $maximum,
            ]
        );

        $ifStmt = $this->builder->throw('RequestValidationException', $exceptionMessage);

        return $this->builder->if($ifCondition, [$ifStmt]);
    }

    protected function generateMinLengthValidation(Field $root): ?Stmt
    {
        $minLength = $root->getMinLength();
        if ($minLength === null) {
            return null;
        }

        $this->addImport(RequestValidationException::class);

        $propertyVar = $this->builder->var($root->getPhpVariableName());
        $ifCondition = $this->builder->compare(
            $this->builder->funcCall('strlen', [$propertyVar]),
            '<',
            $this->builder->val($minLength)
        );

        $exceptionMessage = $this->builder->funcCall(
            'sprintf',
            [
                'Invalid %s value. Given: `%s`, length should be greather than %s',
                $root->getName(),
                $propertyVar,
                $minLength,
            ]
        );

        $ifStmt = $this->builder->throw('RequestValidationException', $exceptionMessage);

        return $this->builder->if($ifCondition, [$ifStmt]);
    }

    protected function generateMaxLengthValidation(Field $root): ?Stmt
    {
        $maxLength = $root->getMaxLength();
        if ($maxLength === null) {
            return null;
        }

        $this->addImport(RequestValidationException::class);

        $propertyVar = $this->builder->var($root->getPhpVariableName());
        $ifCondition = $this->builder->compare(
            $this->builder->funcCall('strlen', [$propertyVar]),
            '>',
            $this->builder->val($maxLength)
        );

        $exceptionMessage = $this->builder->funcCall(
            'sprintf',
            [
                'Invalid %s value. Given: `%s`, length should be less than %s',
                $root->getName(),
                $propertyVar,
                $maxLength,
            ]
        );

        $ifStmt = $this->builder->throw('RequestValidationException', $exceptionMessage);

        return $this->builder->if($ifCondition, [$ifStmt]);
    }

    protected function generatePatternValidation(Field $root): ?Stmt
    {
        $pattern = $root->getPattern();
        if ($pattern === null) {
            return null;
        }

        $this->addImport(RequestValidationException::class);

        $propertyVar = $this->builder->var($root->getPhpVariableName());
        $ifCondition = $this->builder->notEquals(
            $this->builder->funcCall('preg_match', [$this->builder->val($pattern), $propertyVar]),
            $this->builder->val(1)
        );

        $exceptionMessage = $this->builder->funcCall(
            'sprintf',
            [
                'Invalid %s value. Given: `%s`, pattern is %s',
                $root->getName(),
                $propertyVar,
                $pattern,
            ]
        );

        $ifStmt = $this->builder->throw('RequestValidationException', $exceptionMessage);

        return $this->builder->if($ifCondition, [$ifStmt]);
    }

    protected function generateMinItemsValidation(Field $root): ?Stmt
    {
        $minItems = $root->getMinItems();
        if ($minItems === null) {
            return null;
        }

        $this->addImport(RequestValidationException::class);

        $propertyVar = $this->builder->var($root->getPhpVariableName());
        $ifCondition = $this->builder->compare(
            $this->builder->funcCall('count', [$propertyVar]),
            '<',
            $this->builder->val($minItems)
        );

        $exceptionMessage = $this->builder->funcCall(
            'sprintf',
            [
                'Invalid %s value. Expected min items: `%s`',
                $root->getName(),
                $minItems,
            ]
        );

        $ifStmt = $this->builder->throw('RequestValidationException', $exceptionMessage);

        return $this->builder->if($ifCondition, [$ifStmt]);
    }

    protected function generateMaxItemsValidation(Field $root): ?Stmt
    {
        $maxItems = $root->getMaxItems();
        if ($maxItems === null) {
            return null;
        }

        $this->addImport(RequestValidationException::class);

        $propertyVar = $this->builder->var($root->getPhpVariableName());
        $ifCondition = $this->builder->compare(
            $this->builder->funcCall('count', [$propertyVar]),
            '>',
            $this->builder->val($maxItems)
        );

        $exceptionMessage = $this->builder->funcCall(
            'sprintf',
            [
                'Invalid %s value. Expected max items: `%s`',
                $root->getName(),
                $maxItems,
            ]
        );

        $ifStmt = $this->builder->throw('RequestValidationException', $exceptionMessage);

        return $this->builder->if($ifCondition, [$ifStmt]);
    }
}
