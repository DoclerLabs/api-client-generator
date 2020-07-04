<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\SpecObjectInterface;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\FieldStructure;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;

class FieldStructureFactory
{
    public function create(
        SpecObjectInterface $schema,
        ?Field $arrayItem,
        array $objectProperties,
        ?Field $objectParent
    ): FieldStructure {
        $fieldStructure = new FieldStructure();

        $type = $schema->type;
        if ($arrayItem !== null && FieldType::isSpecificationTypeArray($type)) {
            $fieldStructure->setArrayItem($arrayItem);
        } elseif (FieldType::isSpecificationTypeObject($type)) {
            $fieldStructure->setObjectProperties($objectProperties);
        } elseif (isset($schema->enum)) {
            if (!FieldType::isSpecificationTypeString($type)) {
                throw new InvalidSpecificationException('Only string enum fields are currently supported');
            }
            $fieldStructure->setEnumValues($schema->enum);
        }

        if (isset($schema->format)) {
            $fieldStructure->setFormat($schema->format);
        }

        if ($objectParent !== null) {
            $fieldStructure->setObjectParent($objectParent);
        }

        return $fieldStructure;
    }
}
