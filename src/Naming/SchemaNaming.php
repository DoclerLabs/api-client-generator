<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Naming;

use cebe\openapi\spec\Reference;
use cebe\openapi\SpecObjectInterface;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use UnexpectedValueException;

class SchemaNaming
{
    private const OPENAPI_COMPONENT_TYPES = ['schemas', 'parameters'];

    public static function getClassName(SpecObjectInterface $reference, string $fallbackName = ''): string
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
        if (!in_array($referencePath[1], self::OPENAPI_COMPONENT_TYPES, true)) {
            throw new UnexpectedValueException('Only schema and parameter components are supported to be entities.');
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
}
