<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

use cebe\openapi\SpecObjectInterface;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\ConstraintInterface;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Naming\SchemaCollectionNaming;

/**
 * Different schemas can end up with the same class name, e.g. an inline enum property `Call.status` and an inline
 * enum query parameter `callStatus` are both named `CallStatusEnum`. Only one class can be generated for a name, so
 * every other schema with that name gets a numeric suffix (`CallStatus2Enum`), instead of silently using the class
 * of a different schema. Schemas with identical definitions keep sharing their class.
 *
 * The class keeps the definition that it had before: the named component schema, if one of the definitions is a
 * component referenced by its name, otherwise the definition that the generators used to emit (the last one).
 */
class ClassNameCollisionResolver
{
    private const ENUM_SUFFIX = 'Enum';

    public function __construct(private PhpVersion $phpVersion)
    {
    }

    public function resolve(Specification $specification): void
    {
        $fieldsByClassName = [];
        $operationsByField = [];
        foreach ($specification->getOperations() as $operation) {
            foreach ($operation->request->fields as $field) {
                $this->collectNamedFields($field, $operation->name, $fieldsByClassName, $operationsByField);
            }
            foreach ($operation->successfulResponses as $response) {
                if ($response->body !== null) {
                    $this->collectNamedFields($response->body, $operation->name, $fieldsByClassName, $operationsByField);
                }
            }
        }

        $takenClassNames = $this->getTakenClassNames($fieldsByClassName);
        $emittedFields   = $this->getEmittedFields($specification);
        foreach ($fieldsByClassName as $className => $fields) {
            if (count($fields) < 2) {
                continue;
            }

            $definitions = [];
            foreach ($fields as $field) {
                $definitions[$this->getSignature($field)][] = $field;
            }

            if (count($definitions) < 2) {
                continue;
            }

            $keptSignature = $this->getKeptSignature($definitions, $emittedFields[$className] ?? null);
            foreach ($definitions as $signature => $definitionFields) {
                if ($signature === $keptSignature) {
                    continue;
                }

                $newClassName      = $this->getUniqueClassName((string)$className, $takenClassNames);
                $takenClassNames[] = $newClassName;
                $operationNames    = [];
                foreach ($definitionFields as $field) {
                    $field->setReferenceName($newClassName);
                    $operationNames[] = $operationsByField[spl_object_id($field)];
                }

                $warningMessage = sprintf(
                    'Class name %s is shared by different schemas, %s is used for the one in %s. Consider giving the schemas distinct names.',
                    $className,
                    $newClassName,
                    implode(', ', array_unique($operationNames))
                );
                trigger_error($warningMessage, E_USER_WARNING);
            }
        }
    }

    /**
     * @param array<string, Field[]> $fieldsByClassName
     * @param array<int, string>     $operationsByField
     */
    private function collectNamedFields(
        Field $field,
        string $operationName,
        array &$fieldsByClassName,
        array &$operationsByField
    ): void {
        if ($field->isObject() || ($field->isEnum() && $this->phpVersion->isEnumSupported())) {
            $fieldsByClassName[$field->getPhpClassName()][] = $field;
            $operationsByField[spl_object_id($field)]       = $operationName;
        }

        if ($field->isArray()) {
            $this->collectNamedFields($field->getArrayItem(), $operationName, $fieldsByClassName, $operationsByField);
        }

        foreach ($field->getObjectProperties() as $property) {
            $this->collectNamedFields($property, $operationName, $fieldsByClassName, $operationsByField);
        }
    }

    /**
     * The fields whose definition the generators emit for a class name when names collide, see the generators.
     *
     * @return array<string, Field>
     */
    private function getEmittedFields(Specification $specification): array
    {
        $emittedFields   = [];
        $compositeFields = $specification->getCompositeFields()->getUniqueByPhpClassName();
        if ($this->phpVersion->isEnumSupported()) {
            // EnumGenerator
            foreach ($compositeFields as $field) {
                if ($field->isObject() && !$field->isFreeFormObject()) {
                    foreach ($field->getObjectProperties() as $propertyField) {
                        if ($propertyField->isEnum()) {
                            $emittedFields[$propertyField->getPhpClassName()] = $propertyField;
                        }
                    }
                }
                if ($field->isEnum()) {
                    $emittedFields[$field->getPhpClassName()] = $field;
                }
            }
            foreach ($specification->getOperations() as $operation) {
                foreach ($operation->request->fields as $field) {
                    if ($field->isEnum()) {
                        $emittedFields[$field->getPhpClassName()] = $field;
                    }
                }
            }
        }

        // SchemaGenerator and FreeFormSchemaGenerator, registered after the enums
        foreach ($compositeFields as $className => $field) {
            if ($field->isObject()) {
                $emittedFields[$className] = $field;
            }
        }

        return $emittedFields;
    }

    /**
     * @param array<string, Field[]> $definitions
     */
    private function getKeptSignature(array $definitions, ?Field $emittedField): string
    {
        $explicitSignatures = [];
        foreach ($definitions as $signature => $fields) {
            foreach ($fields as $field) {
                if ($field->hasExplicitReferenceName()) {
                    $explicitSignatures[] = (string)$signature;

                    break;
                }
            }
        }

        $candidates       = empty($explicitSignatures) ? array_map('strval', array_keys($definitions)) : $explicitSignatures;
        $emittedSignature = $emittedField === null ? null : $this->getSignature($emittedField);
        if ($emittedSignature !== null && in_array($emittedSignature, $candidates, true)) {
            return $emittedSignature;
        }

        return (string)end($candidates);
    }

    /**
     * @param string[] $takenClassNames
     */
    private function getUniqueClassName(string $className, array $takenClassNames): string
    {
        $suffix = str_ends_with($className, self::ENUM_SUFFIX) ? self::ENUM_SUFFIX : '';
        $base   = substr($className, 0, strlen($className) - strlen($suffix));

        $number = 2;
        while (in_array($base . $number . $suffix, $takenClassNames, true)) {
            ++$number;
        }

        return $base . $number . $suffix;
    }

    /**
     * @param array<string, Field[]> $fieldsByClassName
     *
     * @return string[]
     */
    private function getTakenClassNames(array $fieldsByClassName): array
    {
        $takenClassNames = [];
        foreach ($fieldsByClassName as $className => $fields) {
            $takenClassNames[] = (string)$className;
            foreach ($fields as $field) {
                $takenClassNames[] = SchemaCollectionNaming::getClassName($field->getReferenceName());
            }
        }

        return array_values(array_unique($takenClassNames));
    }

    /**
     * Identifies the definition of the class that a field is generated to: fields with the same signature are
     * generated to the same code (apart from the names of the classes they refer to).
     */
    private function getSignature(Field $field): string
    {
        return serialize($this->describeDefinition($field));
    }

    private function describeDefinition(Field $field): array
    {
        if ($field->isEnum()) {
            $values = $field->getEnumValues() ?? [];
            sort($values);

            return ['enum', $field->getType()->toSpecificationType(), $values];
        }

        if ($field->isObject()) {
            $discriminator = $field->getDiscriminator();

            return [
                'object',
                $field->isFreeFormObject(),
                $field->hasOneOf(),
                $field->hasAnyOf(),
                $discriminator instanceof SpecObjectInterface ? $discriminator->getSerializableData() : $discriminator,
                array_map(fn (Field $property): array => $this->describeProperty($property), $field->getObjectProperties()),
            ];
        }

        if ($field->isArray()) {
            // the name of an inline item depends on the operation, e.g. `{Operation}{Field}Item`
            return ['array', $this->describeProperty($field->getArrayItem(), false)];
        }

        return [$field->getType()->toSpecificationType(), $field->getFormat()];
    }

    private function describeProperty(Field $field, bool $withName = true): array
    {
        $constraints = [];
        foreach ($field->getConstraints() as $constraint) {
            /** @var ConstraintInterface $constraint */
            if ($constraint->exists()) {
                $constraints[] = [get_class($constraint), $constraint->getExceptionMessage()];
            }
        }

        return [
            $withName ? $field->getName() : null,
            $field->isRequired(),
            $field->isNullable(),
            $field->getDefault(),
            $constraints,
            $this->describeDefinition($field),
        ];
    }
}
