<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\SchemaNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;

class EnumGenerator extends MutatorAccessorClassGeneratorAbstract
{
    public const SUBDIRECTORY = 'Schema/';

    public const NAMESPACE_SUBPATH = '\\Schema';

    public static function getCaseName(string $value): string
    {
        $sanitized = self::sanitizeCaseName($value);

        if ($sanitized === '') {
            // no letters, digits or separators, e.g. `*` or `>=`
            return SchemaNaming::getSymbolicEnumValueName($value);
        }

        // a case name cannot start with a digit, and `class` is reserved for the class name constant
        if (preg_match('/^[0-9]/', $sanitized) === 1 || $sanitized === 'CLASS') {
            return 'V_' . $sanitized;
        }

        return $sanitized;
    }

    /**
     * Case names of all values of an enum, with the keys of the values. A symbolic name (a value without letters
     * or digits) that coincides with the name of another value gets a numeric suffix, so that one keeps its name.
     *
     * @param array<int, int|string> $values
     *
     * @return array<int, string>
     */
    public static function getCaseNames(array $values): array
    {
        $caseNames = [];
        foreach ($values as $key => $value) {
            $caseNames[$key] = self::getCaseName((string)$value);
        }

        foreach ($values as $key => $value) {
            if (self::sanitizeCaseName((string)$value) === '') {
                $caseNames[$key] = SchemaNaming::getUniqueName(
                    $caseNames[$key],
                    array_diff_key($caseNames, [$key => true])
                );
            }
        }

        return $caseNames;
    }

    /**
     * @param array<int, int|string> $values
     */
    public static function getCaseNameOfValue(array $values, int|string $value): string
    {
        $key = array_search($value, $values, true);

        return $key === false ? self::getCaseName((string)$value) : self::getCaseNames($values)[$key];
    }

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        if (!$this->phpVersion->isEnumSupported()) {
            return;
        }

        $compositeFields = $specification->getCompositeFields()->getUniqueByPhpClassName();
        foreach ($compositeFields as $field) {
            if (
                $field->isObject()
                && !$field->isFreeFormObject()
            ) {
                foreach ($field->getObjectProperties() as $propertyField) {
                    if ($propertyField->isEnum()) {
                        $this->generateEnum($propertyField, $fileRegistry);
                    }
                }
            }

            if ($field->isEnum()) {
                $this->generateEnum($field, $fileRegistry);
            }
        }
        foreach ($specification->getOperations() as $operation) {
            foreach ($operation->request->fields as $field) {
                if (!empty($field->getEnumValues())) {
                    $this->generateEnum($field, $fileRegistry);
                }
            }
        }
    }

    private function generateEnum(Field $root, PhpFileCollection $fileRegistry): void
    {
        $classBuilder = $this
            ->builder
            ->enum($root->getPhpClassName())
            ->setScalarType($root->getType()->toPhpType())
            ->addStmts($this->generateEnumConsts($root));

        $this->registerFile($fileRegistry, $classBuilder, self::SUBDIRECTORY, self::NAMESPACE_SUBPATH);
    }

    private function generateEnumConsts(Field $root): array
    {
        if ($root->getEnumValues() === null) {
            return [];
        }

        $statements = [];
        $caseNames  = self::getCaseNames($root->getEnumValues());
        foreach ($root->getEnumValues() as $key => $value) {
            $statements[] = $this
                ->builder
                ->enumCase($caseNames[$key])
                ->setValue($value);
        }

        return $statements;
    }

    private static function sanitizeCaseName(string $value): string
    {
        return (string)preg_replace('/[^A-Z0-9_]/', '', strtoupper(str_replace([' ', '-', '/', '.'], '_', $value)));
    }
}
