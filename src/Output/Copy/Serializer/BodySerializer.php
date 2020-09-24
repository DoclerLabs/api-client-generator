<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer;

use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

class BodySerializer
{
    private array $contentTypeSerializers;

    public function __construct()
    {
        $this->contentTypeSerializers = [
            'application/json' => new JsonContentTypeSerializer(),
        ];
    }

    public function getContentTypeSerializer(string $contentType)
    {
        if (!isset($this->contentTypeSerializers[$contentType])) {
            throw new InvalidArgumentException('Content type is not supported: ' . $contentType);
        }

        return $this->contentTypeSerializers[$contentType];
    }

    public function encode(array $requestBody): StreamInterface
    {
    }

    public function decode(StreamInterface $responseBody): array
    {
    }
}