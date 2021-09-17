<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Naming;

class CaseCaster
{
    private const STRING_BREAKDOWN_PATTERN = '/([A-Z][A-Z0-9]*(?=$|[\-_])|[A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z0-9][a-z0-9]*)/';

    public static function toPascal(string $input): string
    {
        if (mb_strlen($input) <= 1) {
            return ucfirst($input);
        }

        $output = [];
        foreach (self::getSubstrings($input) as $match) {
            $output[] = ucfirst(strtolower($match));
        }

        return implode('', $output);
    }

    public static function toCamel(string $input): string
    {
        return lcfirst(self::toPascal($input));
    }

    public static function toSnake(string $input): string
    {
        if (mb_strlen($input) <= 1) {
            return lcfirst($input);
        }

        $output = [];
        foreach (self::getSubstrings($input) as $match) {
            if ($match === strtoupper($match)) {
                $output[] = strtolower($match);
            } else {
                $output[] = lcfirst($match);
            }
        }

        return implode('_', $output);
    }

    public static function toMacro(string $input): string
    {
        return strtoupper(self::toSnake($input));
    }

    private static function getSubstrings(string $input): array
    {
        preg_match_all(self::STRING_BREAKDOWN_PATTERN, $input, $matches);

        return array_filter($matches[0]);
    }
}
