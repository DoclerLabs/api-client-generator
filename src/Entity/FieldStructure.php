<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

class FieldStructure
{
    public const FORMAT_DATE      = 'date';
    public const FORMAT_DATE_TIME = 'date-time';
    private ?Field $arrayItem        = null;
    private array  $objectProperties = [];
    private array  $enumValues       = [];
    private string $format           = '';
    private ?Field $objectParent     = null;

    public function getArrayItem(): ?Field
    {
        return $this->arrayItem;
    }

    public function setArrayItem(Field $arrayItem): self
    {
        $this->arrayItem = $arrayItem;

        return $this;
    }

    public function getObjectProperties(): ?array
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

    public function hasEnumFields(): bool
    {
        if ($this->getObjectProperties() !== null) {
            foreach ($this->getObjectProperties() as $property) {
                $propertyHasEnum = $property->getStructure()->hasEnumFields();
                if ($propertyHasEnum) {
                    return true;
                }
            }
        }

        if ($this->getArrayItem() !== null) {
            $propertyHasEnum = $this->getArrayItem()->getStructure()->hasEnumFields();
            if ($propertyHasEnum) {
                return true;
            }
        }

        return $this->getEnumValues() !== null;
    }

    public function getObjectParent(): ?Field
    {
        return $this->objectParent;
    }

    public function setObjectParent(Field $objectParent): self
    {
        $this->objectParent = $objectParent;

        return $this;
    }

    public function getParentProperties(): array
    {
        $parentProperties = [];
        $parent           = $this->getObjectParent();
        while ($parent !== null) {
            $parentProperties = array_merge($parent->getStructure()->getObjectProperties(), $parentProperties);
            $parent           = $parent->getStructure()->getObjectParent();
        }

        return $parentProperties;
    }
}
