<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientException\RequestValidationException;
use DoclerLabs\ApiClientException\UnexpectedResponseBodyException;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\Json\Json;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\Json\JsonException;
use Psr\Http\Message\ResponseInterface;

class JsonContentTypeSerializer implements ContentTypeSerializerInterface
{
    const JSON_DEPTH   = 512;
    const JSON_OPTIONS = JSON_BIGINT_AS_STRING | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE;

    public function encodeBody(RequestInterface $request): string
    {
        try {
            if ($request->getBody() === null) {
                return '';
            }

            return Json::encode($request->getBody()->toArray(), self::JSON_OPTIONS);
        } catch (JsonException $exception) {
            throw new RequestValidationException($exception->getMessage());
        }
    }

    public function decodeBody(ResponseInterface $response): array
    {
        try {
            $body = $response->getBody();
            $body->rewind();

            return Json::decode($body->getContents(), true, self::JSON_DEPTH, self::JSON_OPTIONS);
        } catch (JsonException $exception) {
            throw new UnexpectedResponseBodyException($exception->getMessage(), $response);
        }
    }
}