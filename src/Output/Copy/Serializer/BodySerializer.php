<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer;

use DoclerLabs\ApiClientException\RequestValidationException;
use DoclerLabs\ApiClientException\UnexpectedResponseException;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\ContentTypeSerializerInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BodySerializer
{
    private const JSON_SUFFIX = '+json';

    /** @var ContentTypeSerializerInterface[] */
    private $contentTypeSerializers = [];

    public function add(ContentTypeSerializerInterface $contentTypeSerializer): self
    {
        $this->contentTypeSerializers[$contentTypeSerializer->getMimeType()] = $contentTypeSerializer;

        return $this;
    }

    /**
     * @throws RequestValidationException
     */
    public function serializeRequest(RequestInterface $request): string
    {
        try {
            $body = $request->getBody();
            if ($body === null) {
                return '';
            }

            return $this->getContentTypeSerializer($request->getContentType())->encode($body);
        } catch (Throwable $exception) {
            throw new RequestValidationException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @throws UnexpectedResponseException
     */
    public function unserializeResponse(ResponseInterface $response): array
    {
        try {
            $body = $response->getBody();
            if ((int)$body->getSize() === 0) {
                return [];
            }

            return $this->getContentTypeSerializer($response->getHeaderLine('Content-Type'))->decode($body);
        } catch (Throwable $exception) {
            throw new UnexpectedResponseException($exception->getMessage(), $response, $exception);
        }
    }

    private function getContentTypeSerializer(string $contentType): ContentTypeSerializerInterface
    {
        $normalizedContentType = $this->normalizeContentType($contentType);

        if (isset($this->contentTypeSerializers[$normalizedContentType])) {
            return $this->contentTypeSerializers[$normalizedContentType];
        }

        // RFC 6839: +json suffix indicates JSON-based format, fall back to JSON serializer
        if ($this->isJsonBasedContentType($normalizedContentType)) {
            return $this->contentTypeSerializers[JsonContentTypeSerializer::MIME_TYPE];
        }

        throw new InvalidArgumentException(
            sprintf(
                'Serializer for `%s` is not found. Supported: %s',
                $normalizedContentType,
                json_encode(array_keys($this->contentTypeSerializers))
            )
        );
    }

    private function normalizeContentType(string $contentType): string
    {
        return strtolower(trim(explode(';', $contentType)[0]));
    }

    private function isJsonBasedContentType(string $normalizedContentType): bool
    {
        return $this->endsWith($normalizedContentType, self::JSON_SUFFIX)
            && isset($this->contentTypeSerializers[JsonContentTypeSerializer::MIME_TYPE]);
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}
