<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Naming;

use DoclerLabs\ApiClientGenerator\Entity\Field;

class SchemaCollectionNaming
{
    private const FILE_SUFFIX = 'Collection';

    public static function getClassName(string $schemaName): string
    {
        return sprintf('%s%s', $schemaName, self::FILE_SUFFIX);
    }

    public static function getArrayDocType(Field $arrayItem): string
    {
        return sprintf('%s[]', $arrayItem->getReferenceName());
    }
}
