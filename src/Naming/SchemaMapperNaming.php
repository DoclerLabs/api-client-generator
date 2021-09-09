<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Naming;

use DoclerLabs\ApiClientGenerator\Entity\Field;

class SchemaMapperNaming
{
    private const FILE_SUFFIX = 'Mapper';

    public static function getClassName(Field $field): string
    {
        return sprintf('%s%s', $field->getPhpClassName(), self::FILE_SUFFIX);
    }

    public static function getPropertyName(Field $field): string
    {
        return sprintf('%s%s', lcfirst($field->getPhpClassName()), self::FILE_SUFFIX);
    }
}
