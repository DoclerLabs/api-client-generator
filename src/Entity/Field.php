<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use DoclerLabs\ApiClientGenerator\Naming\CaseCaster;
use DoclerLabs\ApiClientGenerator\Naming\SchemaCollectionNaming;

class Field
{
    private string         $name;
    private FieldType      $type;
    private string         $referenceName;
    private bool           $required;
    private FieldStructure $structure;
    private bool           $nullable;

    public function __construct(
        string $name,
        FieldType $type,
        string $referenceName,
        bool $required,
        FieldStructure $structure,
        bool $nullable
    ) {
        $this->name          = $name;
        $this->type          = $type;
        $this->referenceName = $referenceName;
        $this->required      = $required;
        $this->structure     = $structure;
        $this->nullable      = $nullable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): FieldType
    {
        return $this->type;
    }

    public function getReferenceName(): string
    {
        return $this->referenceName;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isOptional(): bool
    {
        return !$this->required;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isDate(): bool
    {
        $isDateFormat = $this->structure->getFormat() === FieldStructure::FORMAT_DATE
                        || $this->structure->getFormat() === FieldStructure::FORMAT_DATE_TIME;

        return $this->type->isString() && $isDateFormat;
    }

    public function isObject(): bool
    {
        return $this->type->isObject();
    }

    public function isArrayOfObjects(): bool
    {
        $arrayItem = $this->getStructure()->getArrayItem();

        return $arrayItem !== null
               && $this->type->isArray()
               && $arrayItem->getType()->isObject();
    }

    public function getPhpVariableName(): string
    {
        return CaseCaster::toCamel($this->name);
    }

    public function getPhpClassName(): ?string
    {
        if ($this->type->isObject()) {
            return $this->referenceName;
        }
        $arrayItem = $this->getStructure()->getArrayItem();
        if (
            $arrayItem !== null
            && $arrayItem->getType()->isObject()
        ) {
            return SchemaCollectionNaming::getClassName($arrayItem->getReferenceName());
        }
        if ($this->isDate()) {
            return 'DateTimeInterface';
        }

        return null;
    }

    public function getPhpTypeHint(): string
    {
        if ($this->isNullable()) {
            return '';
        }

        $className = $this->getPhpClassName();
        if ($className === null) {
            return $this->type->toPhpType();
        }

        return $className;
    }

    public function getPhpDocType(bool $allowNullable = true): string
    {
        if ($this->type->isMixed()) {
            return 'mixed';
        }

        if ($this->isComposite()) {
            return $this->getPhpTypeHint();
        }

        $nullableSuffix = '';
        $arraySuffix    = '';
        $typeHint       = $this->getPhpClassName();
        if ($typeHint === null) {
            $typeHint = $this->type->toPhpType();
        }
        if ($allowNullable && ($this->isNullable() || $this->isOptional())) {
            $nullableSuffix = '|null';
        }

        $arrayItem = $this->getStructure()->getArrayItem();
        if ($arrayItem !== null && !$arrayItem->getType()->isObject()) {
            $arraySuffix = '[]';
            $typeHint    = $arrayItem->getPhpTypeHint();
        }

        return sprintf('%s%s%s', $typeHint, $arraySuffix, $nullableSuffix);
    }

    public function getStructure(): FieldStructure
    {
        return $this->structure;
    }

    public function isComposite(): bool
    {
        return $this->isObject() || $this->isArrayOfObjects();
    }

    public function isExtended(): bool
    {
        return $this->getStructure()->getObjectParent() !== null;
    }

    public function getAllProperties()
    {
        $allProperties = $this->getStructure()->getObjectProperties();
        if ($this->isExtended()) {
            $allProperties =
                array_merge(
                    $this->getStructure()->getParentProperties(),
                    $allProperties
                );
        }

        return $allProperties;
    }
}
