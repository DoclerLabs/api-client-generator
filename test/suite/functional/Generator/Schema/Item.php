<?php

namespace Test\Schema;

use JsonSerializable;
use DateTimeInterface;
use DoclerLabs\ApiClientBase\Json\Json;
use DoclerLabs\ApiClientBase\Exception\RequestValidationException;
class Item implements JsonSerializable
{
    const MANDATORY_ENUM_ONE_OPTION = 'one option';
    const MANDATORY_ENUM_ANOTHER_OPTION = 'another option';
    const ALLOWED_MANDATORY_ENUM_LIST = array(self::MANDATORY_ENUM_ONE_OPTION, self::MANDATORY_ENUM_ANOTHER_OPTION);
    const OPTIONAL_ENUM_ONE_OPTION = 'one option';
    const OPTIONAL_ENUM_ANOTHER_OPTION = 'another option';
    const ALLOWED_OPTIONAL_ENUM_LIST = array(self::OPTIONAL_ENUM_ONE_OPTION, self::OPTIONAL_ENUM_ANOTHER_OPTION);
    /** @var int */
    private $mandatoryInteger;
    /** @var string */
    private $mandatoryString;
    /** @var string */
    private $mandatoryEnum;
    /** @var DateTimeInterface */
    private $mandatoryDate;
    /** @var float */
    private $mandatoryFloat;
    /** @var bool */
    private $mandatoryBoolean;
    /** @var string[] */
    private $mandatoryArray;
    /** @var EmbeddedObject */
    private $mandatoryObject;
    /** @var NullableObject|null */
    private $nullableObject;
    /** @var DateTimeInterface|null */
    private $nullableDate;
    /** @var int|null */
    private $optionalInteger;
    /** @var string|null */
    private $optionalString;
    /** @var string|null */
    private $optionalEnum;
    /** @var DateTimeInterface|null */
    private $optionalDate;
    /** @var float|null */
    private $optionalFloat;
    /** @var bool|null */
    private $optionalBoolean;
    /** @var string[]|null */
    private $optionalArray;
    /** @var EmbeddedObject|null */
    private $optionalObject;
    /**
     * @param int $mandatoryInteger
     * @param string $mandatoryString
     * @param string $mandatoryEnum
     * @param DateTimeInterface $mandatoryDate
     * @param float $mandatoryFloat
     * @param bool $mandatoryBoolean
     * @param string[] $mandatoryArray
     * @param EmbeddedObject $mandatoryObject
     * @throws RequestValidationException
    */
    public function __construct(int $mandatoryInteger, string $mandatoryString, string $mandatoryEnum, DateTimeInterface $mandatoryDate, float $mandatoryFloat, bool $mandatoryBoolean, array $mandatoryArray, EmbeddedObject $mandatoryObject)
    {
        $this->mandatoryInteger = $mandatoryInteger;
        $this->mandatoryString = $mandatoryString;
        if (!in_array($mandatoryEnum, self::ALLOWED_MANDATORY_ENUM_LIST, true)) {
            throw new RequestValidationException(sprintf('Invalid %s value. Given: `%s` Allowed: %s', 'mandatoryEnum', $mandatoryEnum, Json::encode(self::ALLOWED_MANDATORY_ENUM_LIST)));
        }
        $this->mandatoryEnum = $mandatoryEnum;
        $this->mandatoryDate = $mandatoryDate;
        $this->mandatoryFloat = $mandatoryFloat;
        $this->mandatoryBoolean = $mandatoryBoolean;
        $this->mandatoryArray = $mandatoryArray;
        $this->mandatoryObject = $mandatoryObject;
    }
    /**
     * @param NullableObject|null $nullableObject
     * @return self
    */
    public function setNullableObject($nullableObject) : self
    {
        $this->nullableObject = $nullableObject;
        return $this;
    }
    /**
     * @param DateTimeInterface|null $nullableDate
     * @return self
    */
    public function setNullableDate($nullableDate) : self
    {
        $this->nullableDate = $nullableDate;
        return $this;
    }
    /**
     * @param int $optionalInteger
     * @return self
    */
    public function setOptionalInteger(int $optionalInteger) : self
    {
        $this->optionalInteger = $optionalInteger;
        return $this;
    }
    /**
     * @param string $optionalString
     * @return self
    */
    public function setOptionalString(string $optionalString) : self
    {
        $this->optionalString = $optionalString;
        return $this;
    }
    /**
     * @param string $optionalEnum
     * @return self
     * @throws RequestValidationException
    */
    public function setOptionalEnum(string $optionalEnum) : self
    {
        if (!in_array($optionalEnum, self::ALLOWED_OPTIONAL_ENUM_LIST, true)) {
            throw new RequestValidationException(sprintf('Invalid %s value. Given: `%s` Allowed: %s', 'optionalEnum', $optionalEnum, Json::encode(self::ALLOWED_OPTIONAL_ENUM_LIST)));
        }
        $this->optionalEnum = $optionalEnum;
        return $this;
    }
    /**
     * @param DateTimeInterface $optionalDate
     * @return self
    */
    public function setOptionalDate(DateTimeInterface $optionalDate) : self
    {
        $this->optionalDate = $optionalDate;
        return $this;
    }
    /**
     * @param float $optionalFloat
     * @return self
    */
    public function setOptionalFloat(float $optionalFloat) : self
    {
        $this->optionalFloat = $optionalFloat;
        return $this;
    }
    /**
     * @param bool $optionalBoolean
     * @return self
    */
    public function setOptionalBoolean(bool $optionalBoolean) : self
    {
        $this->optionalBoolean = $optionalBoolean;
        return $this;
    }
    /**
     * @param string[] $optionalArray
     * @return self
    */
    public function setOptionalArray(array $optionalArray) : self
    {
        $this->optionalArray = $optionalArray;
        return $this;
    }
    /**
     * @param EmbeddedObject $optionalObject
     * @return self
    */
    public function setOptionalObject(EmbeddedObject $optionalObject) : self
    {
        $this->optionalObject = $optionalObject;
        return $this;
    }
    /**
     * @return int
    */
    public function getMandatoryInteger() : int
    {
        return $this->mandatoryInteger;
    }
    /**
     * @return string
    */
    public function getMandatoryString() : string
    {
        return $this->mandatoryString;
    }
    /**
     * @return string
    */
    public function getMandatoryEnum() : string
    {
        return $this->mandatoryEnum;
    }
    /**
     * @return DateTimeInterface
    */
    public function getMandatoryDate() : DateTimeInterface
    {
        return $this->mandatoryDate;
    }
    /**
     * @return float
    */
    public function getMandatoryFloat() : float
    {
        return $this->mandatoryFloat;
    }
    /**
     * @return bool
    */
    public function getMandatoryBoolean() : bool
    {
        return $this->mandatoryBoolean;
    }
    /**
     * @return string[]
    */
    public function getMandatoryArray() : array
    {
        return $this->mandatoryArray;
    }
    /**
     * @return EmbeddedObject
    */
    public function getMandatoryObject() : EmbeddedObject
    {
        return $this->mandatoryObject;
    }
    /**
     * @return NullableObject|null
    */
    public function getNullableObject()
    {
        return $this->nullableObject;
    }
    /**
     * @return DateTimeInterface|null
    */
    public function getNullableDate()
    {
        return $this->nullableDate;
    }
    /**
     * @return int|null
    */
    public function getOptionalInteger()
    {
        return $this->optionalInteger;
    }
    /**
     * @return string|null
    */
    public function getOptionalString()
    {
        return $this->optionalString;
    }
    /**
     * @return string|null
    */
    public function getOptionalEnum()
    {
        return $this->optionalEnum;
    }
    /**
     * @return DateTimeInterface|null
    */
    public function getOptionalDate()
    {
        return $this->optionalDate;
    }
    /**
     * @return float|null
    */
    public function getOptionalFloat()
    {
        return $this->optionalFloat;
    }
    /**
     * @return bool|null
    */
    public function getOptionalBoolean()
    {
        return $this->optionalBoolean;
    }
    /**
     * @return string[]|null
    */
    public function getOptionalArray()
    {
        return $this->optionalArray;
    }
    /**
     * @return EmbeddedObject|null
    */
    public function getOptionalObject()
    {
        return $this->optionalObject;
    }
    /**
     * @return array
    */
    public function jsonSerialize() : array
    {
        $fields = array();
        $fields['mandatoryInteger'] = $this->mandatoryInteger;
        $fields['mandatoryString'] = $this->mandatoryString;
        $fields['mandatoryEnum'] = $this->mandatoryEnum;
        $fields['mandatoryDate'] = $this->mandatoryDate->format(DATE_RFC3339);
        $fields['mandatoryFloat'] = $this->mandatoryFloat;
        $fields['mandatoryBoolean'] = $this->mandatoryBoolean;
        $fields['mandatoryArray'] = $this->mandatoryArray;
        $fields['mandatoryObject'] = $this->mandatoryObject->jsonSerialize();
        $fields['nullableObject'] = $this->nullableObject !== null ? $this->nullableObject->jsonSerialize() : null;
        $fields['nullableDate'] = $this->nullableDate !== null ? $this->nullableDate->format(DATE_RFC3339) : null;
        if ($this->optionalInteger !== null) {
            $fields['optionalInteger'] = $this->optionalInteger;
        }
        if ($this->optionalString !== null) {
            $fields['optionalString'] = $this->optionalString;
        }
        if ($this->optionalEnum !== null) {
            $fields['optionalEnum'] = $this->optionalEnum;
        }
        if ($this->optionalDate !== null) {
            $fields['optionalDate'] = $this->optionalDate->format(DATE_RFC3339);
        }
        if ($this->optionalFloat !== null) {
            $fields['optionalFloat'] = $this->optionalFloat;
        }
        if ($this->optionalBoolean !== null) {
            $fields['optionalBoolean'] = $this->optionalBoolean;
        }
        if ($this->optionalArray !== null) {
            $fields['optionalArray'] = $this->optionalArray;
        }
        if ($this->optionalObject !== null) {
            $fields['optionalObject'] = $this->optionalObject->jsonSerialize();
        }
        return $fields;
    }
}