<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractJsonContentTypeSerializer implements ContentTypeSerializerInterface
{
    private const JSON_DEPTH = 512;
    private const JSON_OPTIONS = JSON_BIGINT_AS_STRING | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE;

    /**
     * @throws SerializeException
     */
    public function encode(SerializableInterface $body): string
    {
        $encodedData = json_encode($body->toArray(), self::JSON_OPTIONS);

        $lastErrorCode = json_last_error();
        if ($lastErrorCode === JSON_ERROR_NONE && $encodedData !== false) {
            return $encodedData;
        }

        throw new SerializeException('JSON encode error: ' . $this->getErrorMessage($lastErrorCode));
    }

    /**
     * @throws SerializeException
     */
    public function decode(StreamInterface $body): array
    {
        $body->rewind();

        // According to RFC7159 a JSON value MUST be an object, array, number, string,
        // or one of the following three literal names: false, null, true.
        $result = json_decode($body->getContents(), true, self::JSON_DEPTH, self::JSON_OPTIONS);

        $lastErrorCode = json_last_error();
        if ($lastErrorCode === JSON_ERROR_NONE) {
            if (!is_array($result)) {
                $result = [
                    ContentTypeSerializerInterface::LITERAL_VALUE_KEY => $result,
                ];
            }

            return $result;
        }

        throw new SerializeException('JSON decode error: ' . $this->getErrorMessage($lastErrorCode));
    }

    private function getErrorMessage(int $errorCode): string
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
}
