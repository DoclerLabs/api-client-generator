<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use InvalidArgumentException;

class FieldType
{
    public const  SPEC_TYPE_STRING     = 'string';
    public const  SPEC_TYPE_FLOAT      = 'number';
    public const  SPEC_TYPE_INTEGER    = 'integer';
    public const  SPEC_TYPE_ARRAY      = 'array';
    public const  SPEC_TYPE_BOOLEAN    = 'boolean';
    public const  SPEC_TYPE_OBJECT     = 'object';
    public const  PHP_TYPE_STRING      = 'string';
    public const  PHP_TYPE_FLOAT       = 'float';
    public const  PHP_TYPE_INTEGER     = 'int';
    public const  PHP_TYPE_ARRAY       = 'array';
    public const  PHP_TYPE_BOOLEAN     = 'bool';
    public const  PHP_TYPE_OBJECT      = 'object';
    private const SPEC_TYPES           = [
        self::SPEC_TYPE_STRING,
        self::SPEC_TYPE_FLOAT,
        self::SPEC_TYPE_INTEGER,
        self::SPEC_TYPE_ARRAY,
        self::SPEC_TYPE_BOOLEAN,
        self::SPEC_TYPE_OBJECT,
    ];
    private const SPEC_TO_PHP_TYPE_MAP = [
        self::SPEC_TYPE_STRING  => self::PHP_TYPE_STRING,
        self::SPEC_TYPE_FLOAT   => self::PHP_TYPE_FLOAT,
        self::SPEC_TYPE_INTEGER => self::PHP_TYPE_INTEGER,
        self::SPEC_TYPE_ARRAY   => self::PHP_TYPE_ARRAY,
        self::SPEC_TYPE_BOOLEAN => self::PHP_TYPE_BOOLEAN,
        self::SPEC_TYPE_OBJECT  => self::PHP_TYPE_OBJECT,
    ];
    private ?string $specificationType;
    private string  $phpType;

    public function __construct(?string $specificationType)
    {
        if (
            $specificationType !== null
            && (
                !isset(self::SPEC_TO_PHP_TYPE_MAP[$specificationType])
                || !in_array($specificationType, self::SPEC_TYPES, true)
            )
        ) {
            throw new InvalidArgumentException('Unknown type passed: ' . $specificationType);
        }

        $this->specificationType = $specificationType;
        $this->phpType           = self::SPEC_TO_PHP_TYPE_MAP[$this->specificationType] ?? '';
    }

    public function toSpecificationType(): ?string
    {
        return $this->specificationType;
    }

    public function toPhpType(): string
    {
        return $this->phpType;
    }

    public function isString(): bool
    {
        return $this->specificationType === self::SPEC_TYPE_STRING
               && $this->phpType === self::PHP_TYPE_STRING;
    }

    public static function isSpecificationTypeString(?string $type): bool
    {
        return $type === self::SPEC_TYPE_STRING;
    }

    public function isFloat(): bool
    {
        return $this->specificationType === self::SPEC_TYPE_FLOAT
               && $this->phpType === self::PHP_TYPE_FLOAT;
    }

    public static function isSpecificationTypeFloat(?string $type): bool
    {
        return $type === self::SPEC_TYPE_FLOAT;
    }

    public function isInteger(): bool
    {
        return $this->specificationType === self::SPEC_TYPE_INTEGER
               && $this->phpType === self::PHP_TYPE_INTEGER;
    }

    public static function isSpecificationTypeInteger(?string $type): bool
    {
        return $type === self::SPEC_TYPE_INTEGER;
    }

    public function isArray(): bool
    {
        return $this->specificationType === self::SPEC_TYPE_ARRAY
               && $this->phpType === self::PHP_TYPE_ARRAY;
    }

    public static function isSpecificationTypeArray(?string $type): bool
    {
        return $type === self::SPEC_TYPE_ARRAY;
    }

    public function isBoolean(): bool
    {
        return $this->specificationType === self::SPEC_TYPE_BOOLEAN
               && $this->phpType === self::PHP_TYPE_BOOLEAN;
    }

    public static function isSpecificationTypeBoolean(?string $type): bool
    {
        return $type === self::SPEC_TYPE_BOOLEAN;
    }

    public function isObject(): bool
    {
        return $this->specificationType === self::SPEC_TYPE_OBJECT
               && $this->phpType === self::PHP_TYPE_OBJECT;
    }

    public static function isSpecificationTypeObject(?string $type): bool
    {
        return $type === self::SPEC_TYPE_OBJECT;
    }

    public function isMixed(): bool
    {
        return $this->specificationType === null
               && $this->phpType === '';
    }

    public static function isSpecificationTypeMixed(?string $type): bool
    {
        return $type === null;
    }
}
