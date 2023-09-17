<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DateTimeImmutable;
use DoclerLabs\ApiClientException\UnexpectedResponseBodyException;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\SchemaMapperNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;

class SchemaMapperGenerator extends MutatorAccessorClassGeneratorAbstract
{
    public const NAMESPACE_SUBPATH = '\\Schema\\Mapper';
    public const SUBDIRECTORY = 'Schema/Mapper/';

    private array $mapMethodThrownExceptions;

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        foreach ($specification->getCompositeResponseFields() as $field) {
            $this->generateMapper($fileRegistry, $field);
        }
    }

    protected function generateMapper(PhpFileCollection $fileRegistry, Field $root): void
    {
        $this->mapMethodThrownExceptions = [];

        $className    = SchemaMapperNaming::getClassName($root);
        $classBuilder = $this->builder
            ->class($className)
            ->implement('SchemaMapperInterface')
            ->addStmts($this->generateProperties($root))
            ->addStmt($this->generateConstructor($root))
            ->addStmt($this->generateMap($root));

        $this->registerFile($fileRegistry, $classBuilder, self::SUBDIRECTORY, self::NAMESPACE_SUBPATH);
    }

    protected function generateProperties(Field $root): array
    {
        $properties = [];
        if ($root->isObject()) {
            $alreadyInjected = [];
            foreach ($root->getObjectProperties() as $child) {
                if ($child->isComposite()) {
                    $childClassName = SchemaMapperNaming::getClassName($child);
                    if (!isset($alreadyInjected[$childClassName])) {
                        $propertyName = SchemaMapperNaming::getPropertyName($child);
                        $properties[] = $this->builder->localProperty($propertyName, $childClassName, $childClassName);

                        $alreadyInjected[$childClassName] = true;
                    }
                }
            }
        }

        if ($root->isArrayOfObjects()) {
            $child = $root->getArrayItem();
            if ($child !== null && $child->isComposite()) {
                $propertyName   = SchemaMapperNaming::getPropertyName($child);
                $childClassName = SchemaMapperNaming::getClassName($child);
                $properties[]   = $this->builder->localProperty(
                    $propertyName,
                    $childClassName,
                    $childClassName
                );
            }
        }

        return $properties;
    }

    protected function generateConstructor(Field $root): ?ClassMethod
    {
        $params     = [];
        $paramInits = [];

        if ($root->isObject()) {
            $alreadyInjected = [];
            foreach ($root->getObjectProperties() as $child) {
                if ($child->isComposite()) {
                    $childClassName = SchemaMapperNaming::getClassName($child);
                    if (!isset($alreadyInjected[$childClassName])) {
                        $propertyName = SchemaMapperNaming::getPropertyName($child);
                        $params[]     = $this->builder
                            ->param($propertyName)
                            ->setType($childClassName)
                            ->getNode();

                        $paramInits[]                     = $this->builder->assign(
                            $this->builder->localPropertyFetch($propertyName),
                            $this->builder->var($propertyName)
                        );
                        $alreadyInjected[$childClassName] = true;
                    }
                }
            }
        }

        if ($root->isArrayOfObjects()) {
            $child = $root->getArrayItem();
            if ($child !== null && $child->isComposite()) {
                $propertyName = SchemaMapperNaming::getPropertyName($child);
                $params[]     = $this->builder
                    ->param($propertyName)
                    ->setType(SchemaMapperNaming::getClassName($child))
                    ->getNode();

                $paramInits[] = $this->builder->assign(
                    $this->builder->localPropertyFetch($propertyName),
                    $this->builder->var($propertyName)
                );
            }
        }

        if (empty($params)) {
            return null;
        }

        return $this->builder
            ->method('__construct')
            ->makePublic()
            ->addParams($params)
            ->addStmts($paramInits)
            ->composeDocBlock($params)
            ->getNode();
    }

    protected function generateMap(Field $root): ClassMethod
    {
        $statements   = [];
        $payloadParam = $this->builder
            ->param('payload')
            ->setType('array')
            ->getNode();

        $payloadVariable = $this->builder->var('payload');
        $this->addImport(
            sprintf(
                '%s%s\\%s',
                $this->baseNamespace,
                SchemaGenerator::NAMESPACE_SUBPATH,
                $root->getPhpClassName()
            )
        );

        if ($root->isFreeFormObject()) {
            $statements[] = $this->generateMapStatementForFreeFormObject($root, $payloadVariable);
        } elseif ($root->isObject()) {
            $statements = array_merge(
                $statements,
                $this->generateMapStatementsForObject($root, $payloadVariable)
            );
        }

        if ($root->isArrayOfObjects()) {
            $statements = array_merge(
                $statements,
                $this->generateMapStatementsForArrayOfObjects($root, $payloadVariable)
            );
        }

        return $this->builder
            ->method('toSchema')
            ->makePublic()
            ->addParam($payloadParam)
            ->addStmts($statements)
            ->setReturnType($root->getPhpTypeHint())
            ->composeDocBlock(
                [$payloadParam],
                $root->getPhpDocType(false),
                array_keys($this->mapMethodThrownExceptions)
            )
            ->getNode();
    }

    protected function generateMapStatementsForArrayOfObjects(
        Field $field,
        Variable $payloadVariable
    ): array {
        $itemsVar     = $this->builder->var('items');
        $statements[] = $this->builder->assign($itemsVar, $this->builder->array([]));

        $payloadItemVariable = $this->builder->var('payloadItem');
        $itemMapper          = $this->builder->localPropertyFetch(
            SchemaMapperNaming::getPropertyName($field->getArrayItem())
        );
        $itemMapperCall      = $this->builder->methodCall($itemMapper, 'toSchema', [$payloadItemVariable]);
        $foreachStatements[] = $this->builder->appendToArray($itemsVar, $itemMapperCall);

        $statements[] = $this->builder->foreach($payloadVariable, $payloadItemVariable, $foreachStatements);

        $statements[] = $this->builder->return(
            $this->builder->new(
                $field->getPhpClassName(),
                [$this->builder->argument($itemsVar, false, true)]
            )
        );

        return $statements;
    }

    protected function generateMapStatementForFreeFormObject(Field $root, Variable $payloadVariable): Stmt
    {
        $schemaInit = $this->builder->new($root->getPhpClassName(), [$payloadVariable]);

        return $this->builder->return($schemaInit);
    }

    protected function generateMapStatementsForObject(Field $root, Variable $payloadVariable): array
    {
        $statements = [];

        $requiredFields        = [];
        $requiredItemsNames    = [];
        $requiredResponseItems = [];
        $optionalFields        = [];
        $optionalResponseItems = [];
        foreach ($root->getObjectProperties() as $property) {
            if ($property->isRequired()) {
                $requiredFields[]        = $property;
                $requiredItemName        = $this->builder->val($property->getName());
                $requiredItemsNames[]    = $requiredItemName;
                $requiredResponseItems[] = $this->builder->getArrayItem($payloadVariable, $requiredItemName);
            } else {
                $optionalFields[]        = $property;
                $optionalResponseItems[] = $this->builder->getArrayItem(
                    $payloadVariable,
                    $this->builder->val($property->getName())
                );
            }
        }

        if (!empty($requiredFields)) {
            $unexpectedResponseBodyException = 'UnexpectedResponseBodyException';
            $this->addImport(UnexpectedResponseBodyException::class);

            $missingFieldsVariable  = $this->builder->var('missingFields');
            $missingFieldsArrayKeys = $this->builder->funcCall('array_keys', [$payloadVariable]);
            $missingFieldsArrayDiff = $this->builder->funcCall(
                'array_diff',
                [$this->builder->array($requiredItemsNames), $missingFieldsArrayKeys]
            );
            $missingFieldsImplode   = $this->builder->funcCall(
                'implode',
                [$this->builder->val(', '), $missingFieldsArrayDiff]
            );

            $statements[] = $this->builder->expr(
                $this->builder->assign($missingFieldsVariable, $missingFieldsImplode)
            );

            $requiredFieldsIfCondition = $this->builder->not(
                $this->builder->funcCall('empty', [$missingFieldsVariable])
            );

            $exceptionMsg = sprintf(
                'Required attributes for `%s` missing in the response body: ',
                $root->getPhpClassName()
            );

            $requiredFieldsIfStatements[] = $this->builder->throw(
                $unexpectedResponseBodyException,
                $this->builder->concat($this->builder->val($exceptionMsg), $missingFieldsVariable)
            );

            $statements[] = $this->builder->if($requiredFieldsIfCondition, $requiredFieldsIfStatements);

            $this->mapMethodThrownExceptions[$unexpectedResponseBodyException] = true;
        }

        $requiredVars = [];
        foreach ($requiredFields as $i => $field) {
            /** @var Field $field */
            if ($field->isComposite()) {
                if ($field->isNullable()) {
                    $requiredVars[] = $this->builder->ternary(
                        $this->builder->notEquals($requiredResponseItems[$i], $this->builder->val(null)),
                        $this->builder->methodCall(
                            $this->builder->localPropertyFetch(SchemaMapperNaming::getPropertyName($field)),
                            'toSchema',
                            [$requiredResponseItems[$i]]
                        ),
                        $this->builder->val(null)
                    );
                } else {
                    $requiredVars[] = $this->builder->methodCall(
                        $this->builder->localPropertyFetch(SchemaMapperNaming::getPropertyName($field)),
                        'toSchema',
                        [$requiredResponseItems[$i]]
                    );
                }
            } elseif ($field->isDate()) {
                $this->addImport(DateTimeImmutable::class);
                if ($field->isNullable()) {
                    $requiredVars[] = $this->builder->ternary(
                        $this->builder->notEquals($requiredResponseItems[$i], $this->builder->val(null)),
                        $this->builder->new('DateTimeImmutable', [$requiredResponseItems[$i]]),
                        $this->builder->val(null)
                    );
                } else {
                    $requiredVars[] = $this->builder->new('DateTimeImmutable', [$requiredResponseItems[$i]]);
                }
            } else {
                $requiredVars[] = $requiredResponseItems[$i];
            }
        }
        $schemaInit = $this->builder->new($root->getPhpClassName(), $requiredVars);

        if (!empty($optionalFields)) {
            $schemaVar    = $this->builder->var('schema');
            $statements[] = $this->builder->assign($schemaVar, $schemaInit);

            foreach ($optionalFields as $i => $field) {
                if ($field->isComposite()) {
                    $mapper = $this->builder->localPropertyFetch(SchemaMapperNaming::getPropertyName($field));
                    if ($field->isNullable()) {
                        $optionalVar = $this->builder->ternary(
                            $this->builder->notEquals($optionalResponseItems[$i], $this->builder->val(null)),
                            $this->builder->methodCall(
                                $mapper,
                                'toSchema',
                                [$optionalResponseItems[$i]]
                            ),
                            $this->builder->val(null)
                        );
                    } else {
                        $optionalVar = $this->builder->methodCall(
                            $mapper,
                            'toSchema',
                            [$optionalResponseItems[$i]]
                        );
                    }
                } elseif ($field->isDate()) {
                    $this->addImport(DateTimeImmutable::class);
                    if ($field->isNullable()) {
                        $optionalVar = $this->builder->ternary(
                            $this->builder->notEquals($optionalResponseItems[$i], $this->builder->val(null)),
                            $this->builder->new('DateTimeImmutable', [$optionalResponseItems[$i]]),
                            $this->builder->val(null)
                        );
                    } else {
                        $optionalVar = $this->builder->new('DateTimeImmutable', [$optionalResponseItems[$i]]);
                    }
                } else {
                    $optionalVar = $optionalResponseItems[$i];
                }

                if ($field->isNullable()) {
                    $ifCondition = $this->builder->funcCall(
                        'array_key_exists',
                        [
                            $field->getName(),
                            $payloadVariable,
                        ]
                    );
                } else {
                    $ifCondition = $this->builder->funcCall('isset', [$optionalResponseItems[$i]]);
                }
                $ifStmt = $this->builder->expr(
                    $this->builder->methodCall(
                        $schemaVar,
                        $this->getSetMethodName($field),
                        [$optionalVar]
                    )
                );
                $statements[] = $this->builder->if($ifCondition, [$ifStmt]);
            }

            $statements[] = $this->builder->return($schemaVar);
        } else {
            $statements[] = $this->builder->return($schemaInit);
        }

        return $statements;
    }
}
