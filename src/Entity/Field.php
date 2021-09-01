<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use DoclerLabs\ApiClientGenerator\Entity\Constraint\ConstraintCollection;
use DoclerLabs\ApiClientGenerator\Naming\CaseCaster;
use DoclerLabs\ApiClientGenerator\Naming\SchemaCollectionNaming;
use RuntimeException;

class Field
{
    public const FORMAT_DATE      = 'date';

    public const FORMAT_DATE_TIME = 'date-time';

    public const TYPE_MIXED       = 'mixed';

    private bool                 $additionalProperties;

    private string               $name;

    private FieldType            $type;

    private ConstraintCollection $constraints;

    private string               $referenceName;

    private bool                 $required;

    private bool                 $nullable;

    private ?Field               $arrayItem        = null;

    private array                $objectProperties = [];

    private array                $enumValues       = [];

    private string               $format           = '';

    /** @phpstan-ignore-next-line cannot use strict type before PHP8 with "mixed" pseudo type */
    private $default;

    public function __construct(
        string $name,
        FieldType $type,
        ConstraintCollection $constraints,
        string $referenceName,
        bool $required,
        bool $nullable,
        bool $additionalProperties
    ) {
        $this->name                 = $name;
        $this->type                 = $type;
        $this->constraints          = $constraints;
        $this->referenceName        = $referenceName;
        $this->required             = $required;
        $this->nullable             = $nullable;
        $this->additionalProperties = $additionalProperties;
    }

    public function getArrayItem(): Field
    {
        if ($this->arrayItem === null) {
            throw new RuntimeException('Call of getArrayItem on the non-array field.');
        }

        return $this->arrayItem;
    }

    public function setArrayItem(Field $arrayItem): self
    {
        $this->arrayItem = $arrayItem;

        return $this;
    }

    /**
     * @return Field[]
     */
    public function getObjectProperties(): array
    {
        return $this->objectProperties;
    }

    public function setObjectProperties(array $objectProperties): self
    {
        $this->objectProperties = $objectProperties;

        return $this;
    }

    public function getEnumValues(): ?array
    {
        return $this->enumValues;
    }

    public function setEnumValues(array $enumValues): self
    {
        $this->enumValues = $enumValues;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): FieldType
    {
        return $this->type;
    }

    public function getConstraints(): ConstraintCollection
    {
        return $this->constraints;
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
        $isDateFormat = $this->getFormat() === self::FORMAT_DATE || $this->getFormat() === self::FORMAT_DATE_TIME;

        return $this->type->isString() && $isDateFormat;
    }

    public function isObject(): bool
    {
        return $this->type->isObject();
    }

    public function isFreeFormObject(): bool
    {
        return $this->isObject() && $this->additionalProperties && count($this->getObjectProperties()) === 0;
    }

    public function isArray(): bool
    {
        return $this->type->isArray();
    }

    public function isArrayOfObjects(): bool
    {
        return $this->isArray()
               && $this->getArrayItem() !== null
               && $this->getArrayItem()->isObject();
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default): self
    {
        $this->default = $default;

        return $this;
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
            && $this->getArrayItem()->isObject()
        ) {
            return SchemaCollectionNaming::getClassName($this->getArrayItem()->getReferenceName());
        }

        if ($this->isDate()) {
            return 'DateTimeInterface';
        }

        throw new RuntimeException('Call of getPhpClassName on the non-composite field.');
    }

    public function getPhpTypeHint(): string
    {
        if ($this->isComposite() || $this->isDate()) {
            return $this->getPhpClassName();
        }

        return $this->type->toPhpType();
    }

    public function getPhpDocType(bool $allowNullable = true): string
    {
        if ($this->type->isMixed()) {
            return self::TYPE_MIXED;
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
            $typeHint    = $this->getArrayItem()->getPhpDocType();
        }

        return sprintf('%s%s%s', $typeHint, $arraySuffix, $nullableSuffix);
    }

    public function isComposite(): bool
    {
        return $this->isObject() || $this->isArrayOfObjects();
    }
}
