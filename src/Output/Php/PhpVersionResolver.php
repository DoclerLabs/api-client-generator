<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Php;

use UnexpectedValueException;

class PhpVersionResolver
{
    public const  VERSION_PHP70                      = '7.0';
    public const  VERSION_PHP71                      = '7.1';
    public const  VERSION_PHP74                      = '7.4';
    public const  VERSIONS                           = [self::VERSION_PHP70, self::VERSION_PHP71, self::VERSION_PHP74];
    private const PARAM_TYPE_HINTS_FORBIDDEN_IN_PHP5 = ['int', 'float', 'string', 'bool', 'object', 'callable'];
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

    public function isParamTypeHintSupported(string $type): bool
    {
        return $this->isVersion7() || !in_array($type, self::PARAM_TYPE_HINTS_FORBIDDEN_IN_PHP5, true);
    }

    public function isReturnTypeHintSupported(): bool
    {
        if ($this->isVersion7()) {
            return true;
        }

        return false;
    }

    public function isVersion70(): bool
    {
        return $this->phpVersion === self::VERSION_PHP7;
    }

    public function isVersion71(): bool
    {
        return $this->phpVersion === self::VERSION_PHP7;
    }

    public function isVersion74(): bool
    {
        return $this->phpVersion === self::VERSION_PHP7;
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }
}
