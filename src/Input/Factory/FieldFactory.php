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

class FieldFactory
{
    private FieldStructureFactory $fieldStructureMapper;
    private PhpNameValidator      $nameValidator;

    public function __construct(FieldStructureFactory $fieldStructureMapper, PhpNameValidator $nameValidator)
    {
        $this->fieldStructureMapper = $fieldStructureMapper;
        $this->nameValidator        = $nameValidator;
    }

    public function create(
        string $operationName,
        string $fieldName,
        SpecObjectInterface $schemaOrReference,
        bool $required,
        string $referenceName = ''
    ): Field {
        try {
            $arrayItem        = null;
            $objectParent     = null;
            $objectProperties = [];
            $schemaReference  = $schemaOrReference;
            $schema           = $this->resolveReference($schemaOrReference);

            $type = $schema->type;
            if (isset($schema->allOf)) {
                if (count($schema->allOf) !== 2) {
                    throw new InvalidSpecificationException('Invalid number of schemas in allOf, only 2 is supported.');
                }
                foreach ($schema->allOf as $allOfSchema) {
                    if ($allOfSchema instanceof Reference) {
                        $objectParentReferenceName = SchemaNaming::getClassName($allOfSchema, $fieldName);

                        $objectParent = $this->create(
                            $operationName,
                            lcfirst($objectParentReferenceName),
                            $allOfSchema,
                            $required,
                            $objectParentReferenceName
                        );
                    } else {
                        $type   = FieldType::PHP_TYPE_OBJECT;
                        $schema = $allOfSchema;
                    }
                }
            }

            if (FieldType::isSpecificationTypeArray($type)) {
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
                    $referenceName = SchemaNaming::getClassName($schemaReference, $fieldName);
                }

                $objectProperties = $this->mapProperties($operationName, $schema);
            }

            if ($fieldName !== '' && !$this->nameValidator->isValidVariableName(CaseCaster::toCamel($fieldName))) {
                throw new InvalidSpecificationException('Invalid field name: ' . $fieldName);
            }

            if ($referenceName !== '' && !$this->nameValidator->isValidClassName($referenceName)) {
                throw new InvalidSpecificationException('Invalid field reference name: ' . $referenceName);
            }

            $fieldStructure = $this->fieldStructureMapper->create(
                $schema,
                $arrayItem,
                $objectProperties,
                $objectParent
            );

            return new Field(
                $fieldName,
                new FieldType($type),
                $referenceName,
                $required,
                $fieldStructure,
                $schema->nullable
            );
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

    protected function mapProperties(string $operationName, SpecObjectInterface $schema): array
    {
        $fields = [];
        foreach ($schema->properties as $childName => $child) {
            $required = false;
            if (isset($schema->required) && is_array($schema->required)) {
                $required = in_array($childName, $schema->required, true);
            }
            $fields[] = $this->create($operationName, $childName, $child, $required, '');
        }

        return $fields;
    }

    private function resolveReference(SpecObjectInterface $schema): SpecObjectInterface
    {
        if ($schema instanceof Reference) {
            $schema = $schema->resolve();
        }
        if ($schema instanceof Parameter) {
            throw new InvalidSpecificationException('Usage of parameter inside of the schema is unexpected.');
        }

        return $schema;
    }
}
