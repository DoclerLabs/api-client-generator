<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;

class EnumGenerator extends MutatorAccessorClassGeneratorAbstract
{
    public const SUBDIRECTORY = 'Schema/';

    public const NAMESPACE_SUBPATH = '\\Schema';

    public static function getCaseName(string $value): string
    {
        $sanitized = preg_replace('[^A-Z0-9_]', '', strtoupper(str_replace([' ', '-', '/', '.'], '_', $value)));

        if (is_numeric($sanitized)) {
            return 'V_' . $sanitized;
        }

        return (string)$sanitized;
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
        foreach ($root->getEnumValues() as $value) {
            $statements[] = $this
                ->builder
                ->enumCase(self::getCaseName((string)$value))
                ->setValue($value);
        }

        return $statements;
    }
}
