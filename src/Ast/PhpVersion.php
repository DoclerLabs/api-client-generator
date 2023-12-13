<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast;

use UnexpectedValueException;

class PhpVersion
{
    public const VERSION_PHP70 = 7.0;

    public const VERSION_PHP71 = 7.1;

    public const VERSION_PHP72 = 7.2;

    public const VERSION_PHP73 = 7.3;

    public const VERSION_PHP74 = 7.4;

    public const VERSION_PHP80 = 8.0;

    private const SUPPORTED_VERSIONS = [
        self::VERSION_PHP70,
        self::VERSION_PHP71,
        self::VERSION_PHP72,
        self::VERSION_PHP73,
        self::VERSION_PHP74,
        self::VERSION_PHP80,
    ];

    public function __construct(private float $phpVersion)
    {
        if (!in_array($phpVersion, self::SUPPORTED_VERSIONS, true)) {
            $versions = json_encode(self::SUPPORTED_VERSIONS, JSON_THROW_ON_ERROR);

            throw new UnexpectedValueException(
                'Unsupported php version ' . $phpVersion . '. Should be one of ' . $versions
            );
        }
    }

    public function isClassConstantVisibilitySupported(): bool
    {
        return $this->isVersionGreaterThanOrEqualTo71();
    }

    public function isNullableTypeHintSupported(): bool
    {
        return $this->isVersionGreaterThanOrEqualTo71();
    }

    public function isVoidReturnTypeSupported(): bool
    {
        return $this->isVersionGreaterThanOrEqualTo71();
    }

    public function isPropertyTypeHintSupported(): bool
    {
        return $this->isVersionGreaterThanOrEqualTo74();
    }


    public function isConstructorPropertyPromotionSupported(): bool
    {
        return $this->isVersionGreaterThanOrEqualTo80();
    }

    public function isMatchSupported(): bool
    {
        return $this->isVersionGreaterThanOrEqualTo80();
    }

    public function isNullSafeSupported(): bool
    {
        return $this->isVersionGreaterThanOrEqualTo80();
    }

    public function isMixedTypehintSupported(): bool
    {
        return $this->isVersionGreaterThanOrEqualTo80();
    }

    private function isVersionGreaterThanOrEqualTo71(): bool
    {
        return $this->phpVersion >= (float)self::VERSION_PHP71;
    }

    private function isVersionGreaterThanOrEqualTo74(): bool
    {
        return $this->phpVersion >= (float)self::VERSION_PHP74;
    }

    private function isVersionGreaterThanOrEqualTo80(): bool
    {
        return $this->phpVersion >= (float)self::VERSION_PHP80;
    }
}
