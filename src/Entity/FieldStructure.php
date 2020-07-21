<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use RuntimeException;

class FieldStructure
{
    public const FORMAT_DATE      = 'date';
    public const FORMAT_DATE_TIME = 'date-time';
    private ?Field $arrayItem        = null;
    private array  $objectProperties = [];
    private array  $enumValues       = [];
    private string $format           = '';
    private ?Field $objectParent     = null;

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
        if ($this->objectProperties === null) {
            throw new RuntimeException('Call of getObjectProperties on the non-object field.');
        }

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
        while ($parent !== null
               && !empty($parent->getStructure()->getObjectProperties())) {
            $parentProperties = array_merge($parent->getStructure()->getObjectProperties(), $parentProperties);
            $parent           = $parent->getStructure()->getObjectParent();
        }

        return $parentProperties;
    }
}
