<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use JsonSerializable;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use UnexpectedValueException;

class SchemaGenerator extends MutatorAccessorClassGeneratorAbstract
{
    public const SUBDIRECTORY = 'Schema/';
    public const NAMESPACE_SUBPATH = '\\Schema';
    private const OPTIONAL_CHANGED_FIELDS_PROPERTY_NAME = 'optionalPropertyChanged';

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $compositeFields = $specification->getCompositeFields()->getUniqueByPhpClassName();
        foreach ($compositeFields as $field) {
            if ($field->isObject() && !$field->isFreeFormObject()) {
                $this->generateSchema($field, $fileRegistry);
            }
        }
    }

    protected function generateSchema(Field $root, PhpFileCollection $fileRegistry): void
    {
        $this->addImport(JsonSerializable::class);

        $className = $root->getPhpClassName();

        $classBuilder = $this->builder
            ->class($className)
            ->implement('SerializableInterface', 'JsonSerializable')
            ->addStmts($this->generateEnumConsts($root))
            ->addStmts($this->generateProperties($root))
            ->addStmt($this->generateOptionalChangedFieldsProperty($root))
            ->addStmt($this->generateConstructor($root))
            ->addStmts($this->generateSetMethods($root))
            ->addStmts($this->generateHasMethods($root))
            ->addStmts($this->generateGetMethods($root))
            ->addStmt($this->generateToArray($root))
            ->addStmt($this->generateJsonSerialize());

        $this->registerFile($fileRegistry, $classBuilder, self::SUBDIRECTORY, self::NAMESPACE_SUBPATH);
    }

    private function generateOptionalChangedFieldsProperty(Field $root): ?Stmt
    {
        $optionalProperties = [];

        foreach ($root->getObjectProperties() as $propertyField) {
            if ($propertyField->isOptional()) {
                if ($propertyField->getPhpVariableName() === self::OPTIONAL_CHANGED_FIELDS_PROPERTY_NAME) {
                    throw new UnexpectedValueException('Property "' . self::OPTIONAL_CHANGED_FIELDS_PROPERTY_NAME . '" not supported!');
                }

                if ($propertyField->isNullable()) {
                    trigger_error('Property "' . $propertyField->getName() . '" is nullable and optional, that might be a sign of a bad api design', E_USER_WARNING);
                }
                $optionalProperties[] = $propertyField;
            }
        }

        if (empty($optionalProperties)) {
            return null;
        }

        $propertyArrayValues = [];
        foreach ($optionalProperties as $optionalProperty) {
            $propertyArrayValues[$optionalProperty->getPhpVariableName()] = $this->builder->val(false);
        }

        return $this->builder->localProperty(
            self::OPTIONAL_CHANGED_FIELDS_PROPERTY_NAME,
            'array',
            'array',
            false,
            $this->builder->array($propertyArrayValues)
        );
    }

    protected function generateEnumConsts(Field $root): array
    {
        $statements = [];
        foreach ($root->getObjectProperties() as $propertyField) {
            foreach ($this->generateEnumStatements($propertyField) as $statement) {
                $statements[] = $statement;
            }
        }

        return $statements;
    }

    protected function generateProperties(Field $root): array
    {
        $statements = [];
        foreach ($root->getObjectProperties() as $propertyField) {
            $statements[] = $this->generateProperty($propertyField);
        }

        return $statements;
    }

    protected function generateConstructor(Field $root): ?ClassMethod
    {
        $params             = [];
        $paramsInit         = [];
        $paramsDoc          = [];
        $thrownExceptionMap = [];

        foreach ($root->getObjectProperties() as $propertyField) {
            if ($propertyField->isRequired()) {
                $validationStmts = $this->generateValidationStmts($propertyField);
                array_push($paramsInit, ...$validationStmts);
                if (!empty($validationStmts)) {
                    $thrownExceptionMap['RequestValidationException'] = true;
                }
                $params[] = $this->builder
                    ->param($propertyField->getPhpVariableName())
                    ->setType($propertyField->getPhpTypeHint(), $propertyField->isNullable())
                    ->getNode();

                $paramsInit[] = $this->builder->assign(
                    $this->builder->localPropertyFetch($propertyField->getPhpVariableName()),
                    $this->builder->var($propertyField->getPhpVariableName())
                );

                $paramsDoc[] = $this->builder
                    ->param($propertyField->getPhpVariableName())
                    ->setType($propertyField->getPhpTypeHint(), $propertyField->isNullable())
                    ->setDocBlockType($propertyField->getPhpDocType($propertyField->isNullable()))
                    ->getNode();
            }
        }
        if (empty($params)) {
            return null;
        }

        return $this->builder
            ->method('__construct')
            ->makePublic()
            ->addParams($params)
            ->addStmts($paramsInit)
            ->composeDocBlock($paramsDoc, '', array_keys($thrownExceptionMap))
            ->getNode();
    }

    protected function generateSetMethods(Field $root): array
    {
        $statements = [];
        foreach ($root->getObjectProperties() as $propertyField) {
            if ($propertyField->isOptional()) {
                $changedFieldSetter = $this->builder->assign(
                    $this->builder->getArrayItem(
                        $this->builder->localPropertyFetch(self::OPTIONAL_CHANGED_FIELDS_PROPERTY_NAME),
                        $this->builder->val($propertyField->getPhpVariableName())
                    ),
                    $this->builder->val(true)
                );

                $statements[] = $this->generateSet($propertyField, [$changedFieldSetter]);
            }
        }

        return $statements;
    }

    protected function generateGetMethods(Field $root): array
    {
        $statements = [];
        foreach ($root->getObjectProperties() as $propertyField) {
            $statements[] = $this->generateGet($propertyField);
        }

        return $statements;
    }

    protected function generateHasMethods(Field $root): array
    {
        $statements = [];
        foreach ($root->getObjectProperties() as $propertyField) {
            if ($propertyField->isOptional()) {
                $statements[] = $this->generateHas($propertyField);
            }
        }

        return $statements;
    }

    protected function generateToArray(Field $root): ClassMethod
    {
        $statements    = [];
        $arrayVariable = $this->builder->var('fields');
        $initialValue  = $this->builder->val([]);

        $statements[] = $this->builder->assign($arrayVariable, $initialValue);
        $statements   = array_merge($statements, $this->collectSerializationFields($root, $arrayVariable));
        $statements[] = $this->builder->return($arrayVariable);

        $returnType = FieldType::PHP_TYPE_ARRAY;

        return $this->builder
            ->method('toArray')
            ->makePublic()
            ->addStmts($statements)
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
    }

    private function generateHas(Field $field): ClassMethod
    {
        $return = $this->builder->return(
            $this->builder->getArrayItem(
                $this->builder->localPropertyFetch(
                    self::OPTIONAL_CHANGED_FIELDS_PROPERTY_NAME
                ),
                $this->builder->val($field->getPhpVariableName())
            )
        );

        return $this->builder
            ->method($this->getHasMethodName($field))
            ->makePublic()
            ->addStmt($return)
            ->setReturnType('bool')
            ->composeDocBlock([], 'bool')
            ->getNode();
    }

    private function collectSerializationFields(Field $root, Variable $arrayVariable): array
    {
        $statements = [];
        foreach ($root->getObjectProperties() as $propertyField) {
            $value = $this->builder->localPropertyFetch($propertyField->getPhpVariableName());
            if ($propertyField->isComposite()) {
                $methodCall = $this->builder->methodCall($value, 'toArray');
                if ($propertyField->isNullable()) {
                    $value = $this->builder->ternary(
                        $this->builder->notEquals($value, $this->builder->val(null)),
                        $methodCall,
                        $this->builder->val(null)
                    );
                } else {
                    $value = $methodCall;
                }
            } elseif ($propertyField->isDate()) {
                $methodCall = $this->builder->methodCall(
                    $value,
                    'format',
                    [$this->builder->constFetch('DATE_RFC3339')]
                );

                if ($propertyField->isNullable()) {
                    $value = $this->builder->ternary(
                        $this->builder->notEquals($value, $this->builder->val(null)),
                        $methodCall,
                        $this->builder->val(null)
                    );
                } else {
                    $value = $methodCall;
                }
            }

            $fieldName       = $this->builder->val($propertyField->getName());
            $assignStatement = $this->builder->appendToAssociativeArray($arrayVariable, $fieldName, $value);

            if ($propertyField->isOptional()) {
                $ifCondition = $this->builder->localMethodCall(
                    $this->getHasMethodName($propertyField)
                );

                $statements[] = $this->builder->if($ifCondition, [$assignStatement]);
            } else {
                $statements[] = $assignStatement;
            }
        }

        return $statements;
    }
}
