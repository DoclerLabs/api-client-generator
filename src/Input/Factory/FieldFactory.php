<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Reference;
use cebe\openapi\SpecObjectInterface;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\ConstraintCollection;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\MaximumConstraint;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\MaxItemsConstraint;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\MaxLengthConstraint;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\MinimumConstraint;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\MinItemsConstraint;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\MinLengthConstraint;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\PatternConstraint;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Input\PhpNameValidator;
use DoclerLabs\ApiClientGenerator\Naming\CaseCaster;
use DoclerLabs\ApiClientGenerator\Naming\SchemaNaming;
use Throwable;
use UnexpectedValueException;

class FieldFactory
{
    public function __construct(private PhpNameValidator $nameValidator, private PhpVersion $phpVersion)
    {
    }

    public function create(
        string $operationName,
        string $fieldName,
        SpecObjectInterface $schemaOrReference,
        bool $required,
        string $referenceName = '',
        string $parentReferenceName = ''
    ): Field {
        try {
            $arrayItem            = null;
            $objectProperties     = [];
            $oneOf                = [];
            $anyOf                = [];
            $additionalProperties = true;
            $schemaReference      = $schemaOrReference;
            $schema               = $this->resolveReference($schemaOrReference);
            $nullable             = (bool)$schema->nullable;

            if ($schemaOrReference instanceof Reference) {
                $referenceName = SchemaNaming::getClassName($schemaOrReference);
            }

            if ($referenceName !== '' && !$this->nameValidator->isValidClassName($referenceName)) {
                throw new InvalidSpecificationException('Invalid field reference name: ' . $referenceName);
            }

            if ($fieldName !== '' && !$this->nameValidator->isValidVariableName(CaseCaster::toCamel($fieldName))) {
                throw new InvalidSpecificationException('Invalid field name: ' . $fieldName);
            }

            $type = $schema->type;
            if (
                is_array($type)
                && count($type) === 2
                && in_array('null', $type, true)
            ) {
                // 3.1 schema nullable does not exist anymore, rather it's done via type
                $nullable = true;
                $type     = array_filter($type, static fn ($type) => $type !== 'null')[0];
            }

            if (
                // if 3.1 and still array, we fallback to mixed type
                is_array($type)
                // same goes for oneOf and anyOf without references
                || (
                    isset($schema->oneOf)
                    && array_filter($schema->oneOf, static fn ($schemaOption) => !($schemaOption instanceof Reference)) !== []
                )
                || (
                    isset($schema->anyOf)
                    && array_filter($schema->anyOf, static fn ($schemaOption) => !($schemaOption instanceof Reference)) !== []
                )
            ) {
                $type = null;
            } elseif (isset($schema->oneOf)) {
                $type = FieldType::SPEC_TYPE_OBJECT;
                $this->processSchemaOptions($schema->oneOf, $operationName, $fieldName, $referenceName, $oneOf, $schema);
            } elseif (isset($schema->anyOf)) {
                $type = FieldType::SPEC_TYPE_OBJECT;
                $this->processSchemaOptions($schema->anyOf, $operationName, $fieldName, $referenceName, $anyOf, $schema);
            } elseif (isset($schema->allOf)) {
                $type = FieldType::SPEC_TYPE_OBJECT;
                if ($referenceName === '') {
                    $referenceName = SchemaNaming::getClassName($schemaReference, $fieldName);
                }

                $objectProperties = $this->mergeAllOfProperties($operationName, $schema);
                $schema           = $this->mergeAllOfAttributes($schema, $nullable);
            } elseif (FieldType::isSpecificationTypeArray($type)) {
                $itemReferenceName = '';
                if ($schema->items === null) {
                    throw new InvalidSpecificationException('Array field does not have items specified.');
                }
                $sibling        = $this->resolveReference($schema->items);
                $itemsReference = $schema->items;
                if (isset($sibling->allOf) || FieldType::isSpecificationTypeObject($sibling->type)) {
                    $itemReferenceName = SchemaNaming::getClassName(
                        $itemsReference,
                        $operationName . ucfirst($fieldName) . 'Item'
                    );
                } elseif ($itemsReference instanceof Reference) {
                    $itemReferenceName = SchemaNaming::getClassName($itemsReference);
                }
                $arrayItem = $this->create(
                    $operationName,
                    lcfirst($itemReferenceName),
                    $sibling,
                    true,
                    $itemReferenceName
                );
            } elseif (FieldType::isSpecificationTypeObject($type)) {
                if ($referenceName === '') {
                    $referenceName = SchemaNaming::getClassName(
                        $schemaReference,
                        sprintf('%s%s', ucfirst($parentReferenceName), ucfirst($fieldName))
                    );
                }

                $objectProperties = $this->mapProperties($operationName, $schema, $referenceName);
            } elseif (!empty($schema->enum)) {
                if ($referenceName === '') {
                    $referenceName = SchemaNaming::getClassName(
                        $schemaReference,
                        sprintf('%s%s', ucfirst($parentReferenceName), ucfirst($fieldName))
                    );
                }
            }
            if (isset($schema->additionalProperties)) {
                if (is_bool($schema->additionalProperties)) {
                    $additionalProperties = $schema->additionalProperties;
                } elseif (is_object($schema->additionalProperties)) {
                    $warningMessage = sprintf(
                        'Additional properties object is not supported: %s.',
                        $referenceName
                    );
                    trigger_error($warningMessage, E_USER_WARNING);
                }
            }

            $fieldType = new FieldType($type, $this->phpVersion);
            $field     = new Field(
                $this->phpVersion,
                $fieldName,
                $fieldType,
                new ConstraintCollection(
                    new MinimumConstraint($schema->minimum, $schema->exclusiveMinimum, $fieldType),
                    new MaximumConstraint($schema->maximum, $schema->exclusiveMaximum, $fieldType),
                    new MinLengthConstraint($schema->minLength),
                    new MaxLengthConstraint($schema->maxLength),
                    new PatternConstraint($schema->pattern),
                    new MinItemsConstraint($schema->minItems),
                    new MaxItemsConstraint($schema->maxItems)
                ),
                $referenceName,
                $required,
                $nullable,
                $additionalProperties,
                !empty($oneOf),
                !empty($anyOf)
            );

            if ($arrayItem !== null) {
                $field->setArrayItem($arrayItem);
            } elseif (!empty($objectProperties)) {
                $field->setObjectProperties($objectProperties);
            } elseif (isset($schema->enum)) {
                $field->setEnumValues($schema->enum);
            } elseif (!empty($oneOf)) {
                $field->setObjectProperties($oneOf);
            } elseif (!empty($anyOf)) {
                $field->setObjectProperties($anyOf);
            }

            if (isset($schema->format)) {
                $field->setFormat($schema->format);
            }

            if (isset($schema->default)) {
                $field->setDefault($schema->default);
            }

            if (isset($schema->discriminator)) {
                $field->setDiscriminator($schema->discriminator);
            }

            return $field;
        } catch (Throwable $exception) {
            throw new InvalidSpecificationException(
                sprintf(
                    'Error on mapping `%s`: %s',
                    $fieldName,
                    $exception->getMessage()
                )
            );
        }
    }

    protected function mapProperties(
        string $operationName,
        SpecObjectInterface $schema,
        string $schemaReferenceName
    ): array {
        $fields = [];
        foreach ($schema->properties as $childName => $child) {
            $required = false;
            if (isset($schema->required) && is_array($schema->required)) {
                $required = in_array($childName, $schema->required, true);
            }
            $fields[] = $this->create($operationName, $childName, $child, $required, '', $schemaReferenceName);
        }

        return $fields;
    }

    private function processSchemaOptions(
        array $schemaOptions,
        string $operationName,
        string $fieldName,
        string &$referenceName,
        array &$options,
        SpecObjectInterface $schemaReference
    ): void {
        if ($referenceName === '') {
            $referenceName = SchemaNaming::getClassName($schemaReference, $fieldName);
        }

        /** @var Reference $schemaOption */
        foreach ($schemaOptions as $schemaOption) {
            $explodedReference = explode('/', $schemaOption->getReference());
            $objectName        = $explodedReference[count($explodedReference) - 1];
            $options[]         = $this->create($operationName, $objectName, $this->resolveReference($schemaOption), false);
        }
    }

    private function resolveReference(SpecObjectInterface $schema): SpecObjectInterface
    {
        $resolved = $schema;
        if ($schema instanceof Reference) {
            $resolved = $schema->resolve();
        }

        if (is_array($resolved) || $resolved === null) {
            throw new UnexpectedValueException('Unexpected value after the reference resolution');
        }

        if ($resolved instanceof Parameter) {
            throw new InvalidSpecificationException('Usage of parameter inside of the schema is unexpected.');
        }

        return $resolved;
    }

    private function mergeAllOfProperties(string $operationName, SpecObjectInterface $schema): array
    {
        $allOfProperties = [];
        foreach ($schema->allOf as $allOfSchema) {
            if ($allOfSchema instanceof Reference) {
                $allOfSchema = $allOfSchema->resolve();
            }
            $allOfProperties[] = $this->mapProperties($operationName, $allOfSchema, '');
        }

        return array_merge([], ...$allOfProperties);
    }

    private function mergeAllOfAttributes(SpecObjectInterface $schema, bool &$nullable): SpecObjectInterface
    {
        foreach ($schema->allOf as $allOfSchema) {
            if ($allOfSchema instanceof Reference) {
                $allOfSchema = $allOfSchema->resolve();
            }

            if (in_array('null', (array)$allOfSchema->type, true)) {
                // 3.1 schema nullable does not exist anymore, rather it's done via type
                $nullable = true;
            } elseif (isset($allOfSchema->nullable)) {
                $nullable = $allOfSchema->nullable;
            }

            // @TODO add more attributes here later if needed
        }

        return $schema;
    }
}
