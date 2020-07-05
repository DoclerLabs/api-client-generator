<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Php;

use UnexpectedValueException;

class PhpVersionResolver
{
    public const  VERSION_PHP70 = '7.0';
    public const  VERSION_PHP71 = '7.1';
    public const  VERSION_PHP74 = '7.4';
    public const  VERSIONS      = [self::VERSION_PHP70, self::VERSION_PHP71, self::VERSION_PHP74];
    private string $phpVersion;

    public function __construct(string $phpVersion)
    {
        if (!in_array($phpVersion, self::VERSIONS, true)) {
            $versions = json_encode(self::VERSIONS, JSON_THROW_ON_ERROR);

            throw new UnexpectedValueException(
                'Unsupported php version ' . $phpVersion . '. Should be one of ' . $versions
            );
        }

        $this->phpVersion = $phpVersion;
    }

    public function isClassConstantVisibilitySupported()
    {
        return $this->isVersion71() || $this->isVersion74();
    }

    public function isNullableTypeHintSupported()
    {
        return $this->isVersion71() || $this->isVersion74();
    }

    public function isVoidTypeHintSupported()
    {
        return $this->isVersion71() || $this->isVersion74();
    }

    public function isParameterTypeHintSupported()
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
