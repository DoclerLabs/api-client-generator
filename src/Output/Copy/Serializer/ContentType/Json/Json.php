<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\Json;

class Json
{
    /** @var int */
    private static $lastErrorCode = JSON_ERROR_NONE;

    /**
     * @param mixed $data    Any data to encode.
     * @param int   $options Optional settings to pass to json_encode (@see json_encode).
     *
     * @return string
     *
     * @throws JsonException
     */
    public static function encode($data, int $options = 0): string
    {
        $encodedData = json_encode($data, $options);

        static::$lastErrorCode = json_last_error();
        if (static::$lastErrorCode === JSON_ERROR_NONE && $encodedData !== false) {
            return $encodedData;
        }

        throw new JsonException('JSON encode error: ' . static::getErrorMessage(static::$lastErrorCode));
    }

    /**
     * @param string $json    The JSON string to decode.
     * @param bool   $assoc   Should force associative array instead of objects.
     * @param int    $depth   Processing depth.
     * @param int    $options Bitmask of JSON decode options.
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public static function decode(string $json, bool $assoc = false, int $depth = 512, int $options = 0)
    {
        $decodedData = json_decode($json, $assoc, $depth, $options);

        static::$lastErrorCode = json_last_error();
        if (static::$lastErrorCode === JSON_ERROR_NONE) {
            return $decodedData;
        }

        throw new JsonException('JSON decode error: ' . static::getErrorMessage(static::$lastErrorCode));
    }

    public static function getLastErrorMessage(): string
    {
        return static::getErrorMessage(static::$lastErrorCode);
    }

    /**
     * @param int $errorCode Error code to check for (@uses JSON_ERROR_*).
     *
     * @return string
     */
    public static function getErrorMessage(int $errorCode): string
    {
        $errorMessages = [
            JSON_ERROR_NONE             => 'No errors',
            JSON_ERROR_DEPTH            => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH   => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR        => 'Unexpected control character found',
            JSON_ERROR_SYNTAX           => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            JSON_ERROR_RECURSION        => 'Recursive reference was found',
            JSON_ERROR_INF_OR_NAN       => 'NaN or Inf was found',
            JSON_ERROR_UNSUPPORTED_TYPE => 'Unsupported type was found',
        ];

        return $errorMessages[$errorCode] ?? 'Unknown error';
    }

    /**
     * @return int The error code for the last action (@see JSON_ERROR_*).
     */
    public static function getLastErrorCode(): int
    {
        return static::$lastErrorCode;
    }
}
