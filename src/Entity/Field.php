<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use DoclerLabs\ApiClientGenerator\Naming\CaseCaster;
use DoclerLabs\ApiClientGenerator\Naming\SchemaCollectionNaming;
use RuntimeException;

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

    public function isArray(): bool
    {
        return $this->type->isArray();
    }

    public function isArrayOfObjects(): bool
    {
        return $this->isArray()
               && $this->getStructure()->getArrayItem()->getType()->isObject();
    }

    public function getPhpVariableName(): string
    {
        return CaseCaster::toCamel($this->name);
    }

    public function getPhpClassName(): string
    {
        if ($this->type->isObject()) {
            return $this->referenceName;
        }

        if (
            $this->type->isArray()
            && $this->getStructure()->getArrayItem()->getType()->isObject()
        ) {
            return SchemaCollectionNaming::getClassName($this->getStructure()->getArrayItem()->getReferenceName());
        }

        if ($this->isDate()) {
            return 'DateTimeInterface';
        }

        throw new RuntimeException('Call of getPhpClassName on the non-composite field.');
    }

    public function getPhpTypeHint(): string
    {
        if ($this->isNullable()) {
            return '';
        }

        if ($this->isComposite() || $this->isDate()) {
            return $this->getPhpClassName();
        }

        return $this->type->toPhpType();
    }

    public function getPhpDocType(bool $allowNullable = true): string
    {
        if ($this->type->isMixed()) {
            return 'mixed';
        }

        $nullableSuffix = '';
        $arraySuffix    = '';
        if ($this->isComposite() || $this->isDate()) {
            $typeHint = $this->getPhpClassName();
        } else {
            $typeHint = $this->type->toPhpType();
        }

        if ($allowNullable && ($this->isNullable() || $this->isOptional())) {
            $nullableSuffix = '|null';
        }

        if ($this->isArray() && !$this->isArrayOfObjects()) {
            $arraySuffix = '[]';
            $typeHint    = $this->getStructure()->getArrayItem()->getPhpTypeHint();
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

    public function getAllProperties(): array
    {
        $allProperties = $this->getStructure()->getObjectProperties() ?? [];
        if (!empty($allProperties) && $this->isExtended()) {
            $allProperties =
                array_merge(
                    $this->getStructure()->getParentProperties(),
                    $allProperties
                );
        }

        return $allProperties;
    }
}
