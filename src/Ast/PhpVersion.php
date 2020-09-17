<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast;

use UnexpectedValueException;

class PhpVersion
{
    public const VERSION_PHP70      = '7.0';
    public const VERSION_PHP71      = '7.1';
    public const VERSION_PHP72      = '7.2';
    public const VERSION_PHP73      = '7.3';
    public const VERSION_PHP74      = '7.4';
    public const SUPPORTED_VERSIONS = [
        self::VERSION_PHP70,
        self::VERSION_PHP71,
        self::VERSION_PHP72,
        self::VERSION_PHP73,
        self::VERSION_PHP74,
    ];
    private string $phpVersion;

    public function __construct(string $phpVersion)
    {
        if (!in_array($phpVersion, self::SUPPORTED_VERSIONS, true)) {
            $versions = json_encode(self::SUPPORTED_VERSIONS, JSON_THROW_ON_ERROR);

            throw new UnexpectedValueException(
                'Unsupported php version ' . $phpVersion . '. Should be one of ' . $versions
            );
        }

        $this->phpVersion = $phpVersion;
    }

    public function isClassConstantVisibilitySupported(): bool
    {
        return $this->isVersion71() || $this->isVersion74();
    }

    public function isNullableTypeHintSupported(): bool
    {
        return $this->isVersion71() || $this->isVersion74();
    }

    public function isVoidTypeHintSupported(): bool
    {
        return $this->isVersion71() || $this->isVersion74();
    }

    public function isParameterTypeHintSupported(): bool
    {
        return $this->isVersion74();
    }

    protected function isVersion71(): bool
    {
        return $this->phpVersion === self::VERSION_PHP71;
    }

    protected function isVersion74(): bool
    {
        return $this->phpVersion === self::VERSION_PHP74;
    }
}
