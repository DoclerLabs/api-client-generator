<?php

namespace Test\Schema;

use JsonSerializable;
class ExtendedItem extends ParentObject
{
    /** @var int */
    private $mandatoryChildInteger;
    /** @var string|null */
    private $optionalChildString;
    /**
     * @param string $madatoryParentString
     * @param int $mandatoryChildInteger
    */
    public function __construct(string $madatoryParentString, int $mandatoryChildInteger)
    {
        parent::__construct($madatoryParentString);
        $this->mandatoryChildInteger = $mandatoryChildInteger;
    }
    /**
     * @param string $optionalChildString
     * @return self
    */
    public function setOptionalChildString(string $optionalChildString) : self
    {
        $this->optionalChildString = $optionalChildString;
        return $this;
    }
    /**
     * @return int
    */
    public function getMandatoryChildInteger() : int
    {
        return $this->mandatoryChildInteger;
    }
    /**
     * @return string|null
    */
    public function getOptionalChildString()
    {
        return $this->optionalChildString;
    }
    /**
     * @return array
    */
    public function jsonSerialize() : array
    {
        $fields = parent::jsonSerialize();
        $fields['mandatoryChildInteger'] = $this->mandatoryChildInteger;
        if ($this->optionalChildString !== null) {
            $fields['optionalChildString'] = $this->optionalChildString;
        }
        return $fields;
    }
}