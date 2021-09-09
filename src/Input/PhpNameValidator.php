<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

class PhpNameValidator
{
    public function isValidVariableName(string $name): bool
    {
        return (bool)preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $name);
    }

    public function isValidClassName(string $name): bool
    {
        return (bool)preg_match('/^[A-Z][a-zA-Z0-9_\x80-\xff]*$/', $name);
    }
}
