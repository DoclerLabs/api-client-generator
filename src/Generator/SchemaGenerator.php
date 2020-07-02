<?php

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use JsonSerializable;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;

class SchemaGenerator extends MutatorAccessorClassGeneratorAbstract
{
    public const SUBDIRECTORY      = 'Schema/';
    public const NAMESPACE_SUBPATH = '\\Schema';
    private string $baseNamespace;

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $this->baseNamespace = $fileRegistry->getBaseNamespace();

        $compositeFields = $specification->getCompositeFields()->getUniqueByPhpClassName();
        foreach ($compositeFields as $field) {
            if ($field->isObject()) {
                $this->generateSchema($field, $fileRegistry);
            }
        }
    }

    protected function generateSchema(Field $field, PhpFileCollection $fileRegistry): void
    {
        $this->addImport(JsonSerializable::class);
        $className = $field->getPhpClassName();

        $classBuilder = $this->builder
            ->class($className)
            ->addStmts($this->generateEnumConsts($field))
            ->addStmts($this->generateProperties($field))
            ->addStmt($this->generateConstructor($field))
            ->addStmts($this->generateSetMethods($field))
            ->addStmts($this->generateGetMethods($field))
            ->addStmt($this->generateJsonSerialize($field));

        $parentClass = $field->getStructure()->getObjectParent();
        if ($parentClass !== null) {
            $classBuilder->extend($parentClass->getPhpClassName());
        } else {
            $classBuilder->implement('JsonSerializable');
        }

        $this->registerFile($fileRegistry, $classBuilder, self::SUBDIRECTORY, self::NAMESPACE_SUBPATH);
    }

    protected function generateEnumConsts(Field $root): array
    {
        $statements = [];
        foreach ($root->getStructure()->getObjectProperties() as $propertyField) {
            foreach ($this->generateEnumStatements($propertyField) as $statement) {
                $statements[] = $statement;
            }
        }

        return $statements;
    }

    protected function generateProperties(Field $root): array
    {
        $statements = [];
        foreach ($root->getStructure()->getObjectProperties() as $propertyField) {
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

        if ($root->isExtended()) {
            $requiredParentPropertyVariables = [];
            foreach ($root->getStructure()->getParentProperties() as $parentClassProperty) {
                if ($parentClassProperty->isRequired()) {
                    $params[] = $this->builder
                        ->param($parentClassProperty->getPhpVariableName())
                        ->setType($parentClassProperty->getPhpTypeHint())
                        ->getNode();

                    $paramsDoc[] = $this->builder
                        ->param($parentClassProperty->getPhpVariableName())
                        ->setType($parentClassProperty->getPhpDocType())
                        ->getNode();

                    $requiredParentPropertyVariables[] = $this->builder
                        ->var($parentClassProperty->getPhpVariableName());
                }
            }

            if (!empty($requiredParentPropertyVariables)) {
                $paramsInit[] = $this->builder->staticCall('parent', '__construct', $requiredParentPropertyVariables);
            }
        }

        foreach ($root->getStructure()->getObjectProperties() as $propertyField) {
            if ($propertyField->isRequired()) {
                $enumStmt = $this->generateEnumValidation($propertyField, $this->baseNamespace);
                if ($enumStmt !== null) {
                    $paramsInit[]                                     = $enumStmt;
                    $thrownExceptionMap['RequestValidationException'] = true;
                }
                $params[] = $this->builder
                    ->param($propertyField->getPhpVariableName())
                    ->setType($propertyField->getPhpTypeHint())
                    ->getNode();

                $paramsInit[] = $this->builder->assign(
                    $this->builder->localPropertyFetch($propertyField->getPhpVariableName()),
                    $this->builder->var($propertyField->getPhpVariableName())
                );

                $paramsDoc[] = $this->builder
                    ->param($propertyField->getPhpVariableName())
                    ->setType($propertyField->getPhpDocType())
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
        foreach ($root->getStructure()->getObjectProperties() as $propertyField) {
            if ($propertyField->isOptional()) {
                $statements[] = $this->generateSet($propertyField, $this->baseNamespace);
            }
        }

        return $statements;
    }

    protected function generateGetMethods(Field $root): array
    {
        $statements = [];
        foreach ($root->getStructure()->getObjectProperties() as $propertyField) {
            $statements[] = $this->generateGet($propertyField);
        }

        return $statements;
    }

    protected function generateJsonSerialize(Field $root): ClassMethod
    {
        $statements    = [];
        $arrayVariable = $this->builder->var('fields');
        $initialValue  = $this->builder->val([]);
        if ($root->isExtended()) {
            $initialValue = $this->builder->staticCall('parent', 'jsonSerialize');
        }

        $statements[] = $this->builder->assign($arrayVariable, $initialValue);
        $statements   = array_merge($statements, $this->collectSerializationFields($root, $arrayVariable));
        $statements[] = $this->builder->return($arrayVariable);

        $returnType = FieldType::PHP_TYPE_ARRAY;

        return $this->builder
            ->method('jsonSerialize')
            ->makePublic()
            ->addStmts($statements)
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
    }

    private function collectSerializationFields(Field $root, Variable $arrayVariable): array
    {
        $statements = [];
        foreach ($root->getStructure()->getObjectProperties() as $propertyField) {
            $value = $this->builder->localPropertyFetch($propertyField->getPhpVariableName());
            if ($propertyField->isComposite()) {
                $methodCall = $this->builder->methodCall($value, 'jsonSerialize');
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

            if ($propertyField->isOptional() && !$propertyField->isNullable()) {
                $ifCondition  = $this->builder->notEquals(
                    $this->builder->localPropertyFetch($propertyField->getPhpVariableName()),
                    $this->builder->val(null)
                );
                $statements[] = $this->builder->if($ifCondition, [$assignStatement]);
            } else {
                $statements[] = $assignStatement;
            }
        }

        return $statements;
    }
}
