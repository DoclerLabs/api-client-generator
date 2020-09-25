<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer;

use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\ContentTypeSerializerInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use InvalidArgumentException;

class BodySerializerRegistry
{
    private array $contentTypeSerializers;

    public function __construct()
    {
        $this->contentTypeSerializers = [
            'application/json'                  => new JsonContentTypeSerializer(),
            'application/x-www-form-urlencoded' => new FormUrlencodedContentTypeSerializer(),
        ];
    }

    public function getContentTypeSerializer(string $contentType): ContentTypeSerializerInterface
    {
        if (!isset($this->contentTypeSerializers[$contentType])) {
            throw new InvalidArgumentException('Content type is not supported: ' . $contentType);
        }

        return $this->contentTypeSerializers[$contentType];
    }
}