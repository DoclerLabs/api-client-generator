<?php

namespace Test\Schema;

use JsonSerializable;
class ExtendedItem implements JsonSerializable
{
    /** @var string */
    private $madatoryParentString;
    /** @var int|null */
    private $optionalParentInteger;
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
        $this->madatoryParentString = $madatoryParentString;
        $this->mandatoryChildInteger = $mandatoryChildInteger;
    }
    /**
     * @param int $optionalParentInteger
     * @return self
    */
    public function setOptionalParentInteger(int $optionalParentInteger) : self
    {
        $this->optionalParentInteger = $optionalParentInteger;
        return $this;
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
     * @return string
    */
    public function getMadatoryParentString() : string
    {
        return $this->madatoryParentString;
    }
    /**
     * @return int|null
    */
    public function getOptionalParentInteger()
    {
        return $this->optionalParentInteger;
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
        $fields = array();
        $fields['madatoryParentString'] = $this->madatoryParentString;
        if ($this->optionalParentInteger !== null) {
            $fields['optionalParentInteger'] = $this->optionalParentInteger;
        }
        $fields['mandatoryChildInteger'] = $this->mandatoryChildInteger;
        if ($this->optionalChildString !== null) {
            $fields['optionalChildString'] = $this->optionalChildString;
        }
        return $fields;
    }
}