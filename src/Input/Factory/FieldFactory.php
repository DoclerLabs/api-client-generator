<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Reference;
use cebe\openapi\SpecObjectInterface;
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
    private PhpNameValidator $nameValidator;

    public function __construct(PhpNameValidator $nameValidator)
    {
        $this->nameValidator = $nameValidator;
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
            $additionalProperties = true;
            $schemaReference      = $schemaOrReference;
            $schema               = $this->resolveReference($schemaOrReference);

            if ($referenceName !== '' && !$this->nameValidator->isValidClassName($referenceName)) {
                throw new InvalidSpecificationException('Invalid field reference name: ' . $referenceName);
            }

            if ($fieldName !== '' && !$this->nameValidator->isValidVariableName(CaseCaster::toCamel($fieldName))) {
                throw new InvalidSpecificationException('Invalid field name: ' . $fieldName);
            }

            $type = $schema->type;
            if (isset($schema->oneOf)) {
                throw new InvalidSpecificationException('oneOf keyword is not currently supported.');
            } elseif (isset($schema->allOf)) {
                $type = FieldType::SPEC_TYPE_OBJECT;
                if ($referenceName === '') {
                    $referenceName = SchemaNaming::getClassName($schemaReference, $fieldName);
                }

                $objectProperties = $this->mergeAllOfProperties($operationName, $schema);
                $schema = $this->mergeAllOfAttributes($schema);
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

            $fieldType = new FieldType($type);
            $field     = new Field(
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
                $schema->nullable,
                $additionalProperties
            );

            if ($arrayItem !== null) {
                $field->setArrayItem($arrayItem);
            } elseif (!empty($objectProperties)) {
                $field->setObjectProperties($objectProperties);
            } elseif (isset($schema->enum)) {
                $field->setEnumValues($schema->enum);
            }

            if (isset($schema->format)) {
                $field->setFormat($schema->format);
            }

            if (isset($schema->default)) {
                $field->setDefault($schema->default);
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

    private function mergeAllOfAttributes(SpecObjectInterface $schema): SpecObjectInterface
    {
        foreach ($schema->allOf as $allOfSchema) {
            if ($allOfSchema instanceof Reference) {
                $allOfSchema = $allOfSchema->resolve();
            }

            if (isset($allOfSchema->nullable)) {
                $schema->nullable = $allOfSchema->nullable;
            }

            // @TODO add more attributes here later if needed
        }

        return $schema;
    }
}
