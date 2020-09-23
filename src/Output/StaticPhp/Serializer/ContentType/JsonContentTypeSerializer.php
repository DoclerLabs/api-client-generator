<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\StaticPhp\Serializer\ContentType;

use DoclerLabs\ApiClientException\RequestValidationException;
use DoclerLabs\ApiClientException\UnexpectedResponseBodyException;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\DecodedResponseBody;
use Psr\Http\Message\StreamInterface;

class JsonContentTypeSerializer implements ContentTypeSerializerInterface
{
    const JSON_DEPTH   = 512;
    const JSON_OPTIONS = JSON_BIGINT_AS_STRING | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE;

    public function getContentType(): string
    {
        return 'application/json';
    }

    public function encode(array $requestBody): StreamInterface
    {
        try {
            $string = Json::encode($requestBody, self::JSON_OPTIONS);

            $stream = fopen('php://memory', 'rb+');
            fwrite($stream, $string);
            rewind($stream);

            return new EncodedRequestBody($stream);
        } catch (JsonException $exception) {
            throw new RequestValidationException($exception->getMessage());
        }
    }

    public function decode(StreamInterface $responseBody): DecodedResponseBody
    {
        try {
            return Json::decode($responseBody->getContents(), true, self::JSON_DEPTH, self::JSON_OPTIONS);
        } catch (JsonException $exception) {
            throw new UnexpectedResponseBodyException($exception->getMessage());
        }
    }
}