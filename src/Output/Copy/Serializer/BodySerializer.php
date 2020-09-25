<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer;

use DoclerLabs\ApiClientException\RequestValidationException;
use DoclerLabs\ApiClientException\UnexpectedResponseBodyException;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\ContentTypeSerializerInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\Json\Json;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BodySerializer
{
    private array $contentTypeSerializers;

    public function __construct()
    {
        $this->contentTypeSerializers = [
            'application/json'                  => new JsonContentTypeSerializer(),
            'application/x-www-form-urlencoded' => new FormUrlencodedContentTypeSerializer(),
        ];
    }

    /**
     * @throws RequestValidationException
     */
    public function encodeBody(RequestInterface $request): string
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
    public function decodeBody(ResponseInterface $response): array
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
                sprintf(
                    'Serializer for `%s` is not found. Supported: %s',
                    $contentType,
                    Json::encode(array_keys($this->contentTypeSerializers))
                )
            );
        }

        return $this->contentTypeSerializers[$contentType];
    }
}