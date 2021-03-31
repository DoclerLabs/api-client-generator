<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Reference;
use cebe\openapi\SpecObjectInterface;
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
            $arrayItem        = null;
            $objectProperties = [];
            $schemaReference  = $schemaOrReference;
            $schema           = $this->resolveReference($schemaOrReference);

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
                    $required,
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

            $field = new Field(
                $fieldName,
                new FieldType($type),
                $referenceName,
                $required,
                $schema->nullable,
                $schema->minimum,
                $schema->exclusiveMinimum,
                $schema->maximum,
                $schema->exclusiveMaximum,
                $schema->minLength,
                $schema->maxLength,
                $schema->pattern,
                $schema->minItems,
                $schema->maxItems
            );

            if ($arrayItem !== null) {
                $field->setArrayItem($arrayItem);
            } elseif (!empty($objectProperties)) {
                $field->setObjectProperties($objectProperties);
            } elseif (isset($schema->enum)) {
                if (!FieldType::isSpecificationTypeString($type)) {
                    throw new InvalidSpecificationException('Only string enum fields are currently supported');
                }
                $field->setEnumValues($schema->enum);
            }

            if (isset($schema->format)) {
                $field->setFormat($schema->format);
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
}
