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

    private const RESERVED_CLASS_NAME_SUFFIX = 'Schema';

    /**
     * Words that cannot be used as a class name (keywords and reserved type names) in at least one PHP version.
     * The soft reserved words (enum, resource, numeric) are still valid class names, so they are not listed.
     */
    private const RESERVED_CLASS_NAMES = [
        'abstract', 'and', 'array', 'as', 'bool', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const',
        'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor',
        'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'false', 'final', 'finally',
        'float', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once',
        'instanceof', 'insteadof', 'int', 'interface', 'isset', 'iterable', 'list', 'match', 'mixed', 'namespace',
        'never', 'new', 'null', 'object', 'or', 'parent', 'print', 'private', 'protected', 'public', 'readonly',
        'require', 'require_once', 'return', 'self', 'static', 'string', 'switch', 'throw', 'trait', 'true', 'try',
        'unset', 'use', 'var', 'void', 'while', 'xor', 'yield',
    ];

    private const EMPTY_ENUM_VALUE_NAME = 'EMPTY';

    private const SYMBOL_NAMES = [
        ' '  => 'SPACE',
        '!'  => 'EXCLAMATION',
        '"'  => 'QUOTE',
        '#'  => 'HASH',
        '$'  => 'DOLLAR',
        '%'  => 'PERCENT',
        '&'  => 'AMPERSAND',
        '\'' => 'APOSTROPHE',
        '('  => 'LEFT_PARENTHESIS',
        ')'  => 'RIGHT_PARENTHESIS',
        '*'  => 'ASTERISK',
        '+'  => 'PLUS',
        ','  => 'COMMA',
        '-'  => 'MINUS',
        '.'  => 'DOT',
        '/'  => 'SLASH',
        ':'  => 'COLON',
        ';'  => 'SEMICOLON',
        '<'  => 'LESS_THAN',
        '='  => 'EQUALS',
        '>'  => 'GREATER_THAN',
        '?'  => 'QUESTION_MARK',
        '@'  => 'AT',
        '['  => 'LEFT_BRACKET',
        '\\' => 'BACKSLASH',
        ']'  => 'RIGHT_BRACKET',
        '^'  => 'CARET',
        '_'  => 'UNDERSCORE',
        '`'  => 'BACKTICK',
        '{'  => 'LEFT_BRACE',
        '|'  => 'PIPE',
        '}'  => 'RIGHT_BRACE',
        '~'  => 'TILDE',
    ];

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

    /**
     * Class name of an object schema. A schema name that is a reserved word in PHP (e.g. `Match`) gets a `Schema`
     * suffix; the names derived from the schema name (e.g. `MatchCollection`) are valid and stay unchanged.
     */
    public static function getSchemaClassName(string $schemaName): string
    {
        return self::isReservedClassName($schemaName) ? $schemaName . self::RESERVED_CLASS_NAME_SUFFIX : $schemaName;
    }

    public static function isReservedClassName(string $name): bool
    {
        return in_array(strtolower($name), self::RESERVED_CLASS_NAMES, true);
    }

    public static function getEnumConstName(Field $field, string $enum): string
    {
        return sprintf(
            '%s_%s',
            CaseCaster::toMacro($field->getName()),
            CaseCaster::toMacro($enum)
        );
    }

    /**
     * Constant name for an enum value that yields no valid name from its letters and digits (e.g. `*`).
     */
    public static function getSymbolicEnumConstName(Field $field, string $enum): string
    {
        return sprintf(
            '%s_%s',
            CaseCaster::toMacro($field->getName()),
            self::getSymbolicEnumValueName($enum)
        );
    }

    /**
     * Name for an enum value without letters or digits, built from the names of its characters,
     * e.g. `*` -> ASTERISK, `>=` -> GREATER_THAN_EQUALS, `€` -> U20AC and the empty string -> EMPTY.
     */
    public static function getSymbolicEnumValueName(string $value): string
    {
        if ($value === '') {
            return self::EMPTY_ENUM_VALUE_NAME;
        }

        $names = [];
        foreach (mb_str_split($value, 1, 'UTF-8') as $character) {
            if (isset(self::SYMBOL_NAMES[$character])) {
                $names[] = self::SYMBOL_NAMES[$character];
            } elseif (mb_check_encoding($character, 'UTF-8')) {
                $names[] = sprintf('U%04X', mb_ord($character, 'UTF-8'));
            } else {
                $names[] = 'X' . strtoupper(bin2hex($character));
            }
        }

        return implode('_', $names);
    }

    /**
     * Returns the name, or when it is already taken, the name with the first free numeric suffix.
     *
     * @param string[] $takenNames
     */
    public static function getUniqueName(string $name, array $takenNames, string $separator = '_'): string
    {
        $uniqueName = $name;
        for ($suffix = 2; in_array($uniqueName, $takenNames, true); ++$suffix) {
            $uniqueName = $name . $separator . $suffix;
        }

        return $uniqueName;
    }
}
