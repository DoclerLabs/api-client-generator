<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace OpenApi\PetStoreClient\Serializer;

use DoclerLabs\ApiClientException\RequestValidationException;
use DoclerLabs\ApiClientException\UnexpectedResponseException;
use InvalidArgumentException;
use OpenApi\PetStoreClient\Request\RequestInterface;
use OpenApi\PetStoreClient\Serializer\ContentType\ContentTypeSerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BodySerializer
{
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
            if ((int) $body->getSize() === 0) {
                return [];
            }

            return $this->getContentTypeSerializer($response->getHeaderLine('Content-Type'))->decode($body);
        } catch (Throwable $exception) {
            throw new UnexpectedResponseException($exception->getMessage(), $response, $exception);
        }
    }

    private function getContentTypeSerializer(string $contentType): ContentTypeSerializerInterface
    {
        $contentType = strtolower(trim(explode(';', $contentType)[0]));
        if (!isset($this->contentTypeSerializers[$contentType])) {
            throw new InvalidArgumentException(sprintf('Serializer for `%s` is not found. Supported: %s', $contentType, json_encode(array_keys($this->contentTypeSerializers))));
        }

        return $this->contentTypeSerializers[$contentType];
    }
}
