<?php

namespace DoclerLabs\ApiClientGenerator\Naming;

use DoclerLabs\ApiClientGenerator\Entity\Field;
use UnexpectedValueException;

class ResponseMapperNaming
{
    private const FILE_SUFFIX = 'ResponseMapper';

    public static function getClassName(Field $field): string
    {
        if ($field->getPhpClassName() === null) {
            throw new UnexpectedValueException('Passed field is not a composite field.');
        }

        return sprintf('%s%s', $field->getPhpClassName(), self::FILE_SUFFIX);
    }

    public static function getPropertyName(Field $field): string
    {
        if ($field->getPhpClassName() === null) {
            throw new UnexpectedValueException('Passed field is not a composite field.');
        }

        return sprintf('%s%s', lcfirst($field->getPhpClassName()), self::FILE_SUFFIX);
    }
}
