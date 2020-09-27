<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer;

use DoclerLabs\ApiClientException\RequestValidationException;
use DoclerLabs\ApiClientException\UnexpectedResponseBodyException;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\ContentTypeSerializerInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\Json\Json;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BodySerializer
{
    private $contentTypeSerializers;

    public function add(string $contentType, ContentTypeSerializerInterface $contentTypeSerializer): self
    {
        $this->contentTypeSerializers[$contentType] = $contentTypeSerializer;

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
            throw new RequestValidationException($exception->getMessage());
        }
    }

    /**
     * @throws UnexpectedResponseBodyException
     */
    public function unserializeResponse(ResponseInterface $response): array
    {
        try {
            $body = $response->getBody();
            if ($body === null || (int)$body->getSize() === 0) {
                return [];
            }

            return $this->getContentTypeSerializer($response->getHeaderLine('Content-Type'))->decode($body);
        } catch (Throwable $exception) {
            throw new UnexpectedResponseBodyException($exception->getMessage(), $response);
        }
    }

    private function getContentTypeSerializer(string $contentType): ContentTypeSerializerInterface
    {
        if (!isset($this->contentTypeSerializers[$contentType])) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Serializer for `%s` is not found. Supported: %s',
                    $contentType,
                    Json::encode(\array_keys($this->contentTypeSerializers))
                )
            );
        }

        return $this->contentTypeSerializers[$contentType];
    }
}
