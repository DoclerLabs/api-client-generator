<?php

namespace DoclerLabs\ApiClientGenerator\Naming;

use cebe\openapi\spec\Reference;
use cebe\openapi\SpecObjectInterface;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use UnexpectedValueException;

class SchemaNaming
{
    private const ALLOWED_ENUM_PREFIX    = 'ALLOWED';
    private const ALLOWED_ENUM_SUFFIX    = 'LIST';
    private const OPENAPI_COMPONENT_TYPE = 'schemas';

    public static function getClassName(SpecObjectInterface $reference, string $fallbackName): string
    {
        if (!($reference instanceof Reference)) {
            $fallbackName   = CaseCaster::toPascal($fallbackName);
            $warningMessage = sprintf(
                'Fallback naming used: %s. Consider extracting the object to a separate schema to set the name explicitly.',
                $fallbackName
            );
            trigger_error($warningMessage, E_USER_WARNING);

            return $fallbackName;
        }

        $referencePath = $reference->getReference();
        $referencePath = explode('/', $referencePath);
        $referencePath = array_reverse($referencePath);
        if ($referencePath[1] !== self::OPENAPI_COMPONENT_TYPE) {
            throw new UnexpectedValueException('Only schema components are supported to be entities.');
        }

        return CaseCaster::toPascal($referencePath[0]);
    }

    public static function getEnumConstName(Field $field, string $enum): string
    {
        return sprintf(
            '%s_%s',
            CaseCaster::toMacro($field->getName()),
            CaseCaster::toMacro($enum)
        );
    }

    public static function getAllowedEnumConstName(Field $field): string
    {
        return sprintf(
            '%s_%s_%s',
            self::ALLOWED_ENUM_PREFIX,
            CaseCaster::toMacro($field->getName()),
            self::ALLOWED_ENUM_SUFFIX
        );
    }
}
