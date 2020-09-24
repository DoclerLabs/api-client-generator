<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DateTimeImmutable;
use DoclerLabs\ApiClientException\UnexpectedResponseBodyException;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ResponseMapperNaming;
use DoclerLabs\ApiClientGenerator\Output\Copy\Response\Mapper\ResponseMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;

class ResponseMapperGenerator extends MutatorAccessorClassGeneratorAbstract
{
    public const NAMESPACE_SUBPATH = '\\Response\\Mapper';
    public const SUBDIRECTORY      = 'Response/Mapper/';
    private array $mapMethodThrownExceptions;

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        foreach ($specification->getCompositeResponseFields() as $field) {
            $this->generateMapper($fileRegistry, $field);
        }
    }

    protected function generateMapper(PhpFileCollection $fileRegistry, Field $root): void
    {
        $this
            ->addImport(ResponseMapperInterface::class);

        $this->mapMethodThrownExceptions = [];

        $className    = ResponseMapperNaming::getClassName($root);
        $classBuilder = $this->builder
            ->class($className)
            ->implement('ResponseMapperInterface')
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
                    $childClassName = ResponseMapperNaming::getClassName($child);
                    if (!isset($alreadyInjected[$childClassName])) {
                        $propertyName = ResponseMapperNaming::getPropertyName($child);
                        $properties[] = $this->builder->localProperty($propertyName, $childClassName, $childClassName);

                        $alreadyInjected[$childClassName] = true;
                    }
                }
            }
        }

        if ($root->isArrayOfObjects()) {
            $child = $root->getArrayItem();
            if ($child !== null && $child->isComposite()) {
                $propertyName   = ResponseMapperNaming::getPropertyName($child);
                $childClassName = ResponseMapperNaming::getClassName($child);
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
                    $childClassName = ResponseMapperNaming::getClassName($child);
                    if (!isset($alreadyInjected[$childClassName])) {
                        $propertyName = ResponseMapperNaming::getPropertyName($child);
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
                $propertyName = ResponseMapperNaming::getPropertyName($child);
                $params[]     = $this->builder
                    ->param($propertyName)
                    ->setType(ResponseMapperNaming::getClassName($child))
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
        $statements    = [];
        $returnObj     = null;
        $responseParam = $this->builder
            ->param('response')
            ->setType('Response')
            ->getNode();
        $responseVar   = $this->builder->var('response');
        $payloadVar    = $this->builder->var('payload');

        $statements[] = $this->builder->assign($payloadVar, $this->builder->methodCall($responseVar, 'getPayload'));

        $this->addImport(
            sprintf(
                '%s%s\\%s',
                $this->baseNamespace,
                SchemaGenerator::NAMESPACE_SUBPATH,
                $root->getPhpClassName()
            )
        );

        if ($root->isObject()) {
            $statements =
                array_merge(
                    $statements,
                    $this->generateMapStatementsForObject($root, $payloadVar, $responseVar)
                );
        }

        if ($root->isArrayOfObjects()) {
            $statements =
                array_merge(
                    $statements,
                    $this->generateMapStatementsForArrayOfObjects($root, $payloadVar, $responseVar)
                );
        }

        return $this->builder
            ->method('map')
            ->makePublic()
            ->addParam($responseParam)
            ->addStmts($statements)
            ->setReturnType($root->getPhpTypeHint(), $root->isNullable())
            ->composeDocBlock(
                [$responseParam],
                $root->getPhpDocType(false),
                array_keys($this->mapMethodThrownExceptions)
            )
            ->getNode();
    }

    protected function generateMapStatementsForArrayOfObjects(
        Field $field,
        Variable $payloadVar,
        Variable $responseVar
    ): array {
        $itemsVar     = $this->builder->var('items');
        $statements[] = $this->builder->assign($itemsVar, $this->builder->array([]));

        $as             = $this->builder->var('payloadItem');
        $itemMapper     = $this->builder->localPropertyFetch(
            ResponseMapperNaming::getPropertyName($field->getArrayItem())
        );
        $statusCodeCall = $this->builder->methodCall($responseVar, 'getStatusCode');

        $itemMapperCall      =
            $this->builder->methodCall(
                $itemMapper,
                'map',
                [$this->builder->new('Response', $this->builder->args([$statusCodeCall, $as]))]
            );
        $foreachStatements[] = $this->builder->appendToArray($itemsVar, $itemMapperCall);

        $statements[] = $this->builder->foreach($payloadVar, $as, $foreachStatements);

        $statements[] = $this->builder->return(
            $this->builder->new(
                $field->getPhpClassName(),
                [$this->builder->argument($itemsVar, false, true)]
            )
        );

        return $statements;
    }

    protected function generateMapStatementsForObject(Field $root, Variable $payloadVar, Variable $responseVar): array
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
                $requiredResponseItems[] = $this->builder->getArrayItem($payloadVar, $requiredItemName);
            } else {
                $optionalFields[]        = $property;
                $optionalResponseItems[] = $this->builder->getArrayItem(
                    $payloadVar,
                    $this->builder->val($property->getName())
                );
            }
        }

        if (!empty($requiredFields)) {
            $unexpectedResponseBodyException = 'UnexpectedResponseBodyException';
            $this->addImport(UnexpectedResponseBodyException::class);

            $requiredIsset = $this->builder->not(
                $this->builder->funcCall('isset', $requiredResponseItems)
            );

            $missingFieldsVar       = $this->builder->var('missingFields');
            $missingFieldsArrayKeys = $this->builder->funcCall('array_keys', [$payloadVar]);
            $missingFieldsArrayDiff = $this->builder->funcCall(
                'array_diff',
                [$this->builder->array($requiredItemsNames), $missingFieldsArrayKeys]
            );
            $missingFieldsImplode   = $this->builder->funcCall(
                'implode',
                [$this->builder->val(', '), $missingFieldsArrayDiff]
            );

            $exceptionMsg = sprintf(
                'Required attributes for `%s` missing in the response body: ',
                $root->getPhpClassName()
            );

            $ifStmts[] = $this->builder->expr($this->builder->assign($missingFieldsVar, $missingFieldsImplode));
            $ifStmts[] = $this->builder->throw(
                $unexpectedResponseBodyException,
                $this->builder->concat($this->builder->val($exceptionMsg), $missingFieldsVar)
            );

            $statements[] = $this->builder->if($requiredIsset, $ifStmts);

            $this->mapMethodThrownExceptions[$unexpectedResponseBodyException] = true;
        }

        $requiredVars = [];
        foreach ($requiredFields as $i => $field) {
            /** @var Field $field */
            if ($field->isComposite()) {
                $requiredVars[] = $this->builder->methodCall(
                    $this->builder->localPropertyFetch(ResponseMapperNaming::getPropertyName($field)),
                    'map',
                    [
                        $this->builder->new(
                            'Response',
                            $this->builder->args(
                                [
                                    $this->builder->methodCall($responseVar, 'getStatusCode'),
                                    $requiredResponseItems[$i],
                                ]
                            )
                        ),
                    ]
                );
            } elseif ($field->isDate()) {
                $this->addImport(DateTimeImmutable::class);
                $requiredVars[] = $this->builder->new('DateTimeImmutable', [$requiredResponseItems[$i]]);
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
                    $mapper      = $this->builder->localPropertyFetch(ResponseMapperNaming::getPropertyName($field));
                    $optionalVar = $this->builder->methodCall(
                        $mapper,
                        'map',
                        [
                            $this->builder->new(
                                'Response',
                                $this->builder->args(
                                    [
                                        $this->builder->methodCall($responseVar, 'getStatusCode'),
                                        $optionalResponseItems[$i],
                                    ]
                                )
                            ),
                        ]
                    );
                } elseif ($field->isDate()) {
                    $this->addImport(DateTimeImmutable::class);
                    $optionalVar = $this->builder->new('DateTimeImmutable', [$optionalResponseItems[$i]]);
                } else {
                    $optionalVar = $optionalResponseItems[$i];
                }

                $ifCondition  = $this->builder->funcCall('isset', [$optionalResponseItems[$i]]);
                $ifStmt       = $this->builder->expr(
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
