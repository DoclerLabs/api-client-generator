<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\VdnApiJsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\XmlContentTypeSerializer;

class Response
{
    private const ALLOWED_CONTENT_TYPES = [
        JsonContentTypeSerializer::MIME_TYPE,
        FormUrlencodedContentTypeSerializer::MIME_TYPE,
        XmlContentTypeSerializer::MIME_TYPE,
        VdnApiJsonContentTypeSerializer::MIME_TYPE,
    ];

    private int    $statusCode;
    private ?Field $body;
    private array  $bodyContentTypes;

    public function __construct(int $statusCode, Field $body = null, array $bodyContentTypes = [])
    {
        $this->statusCode = $statusCode;
        $this->body       = $body;

        $unsupportedContentTypes = array_diff($bodyContentTypes, static::ALLOWED_CONTENT_TYPES);
        if (!empty($unsupportedContentTypes)) {
            throw new InvalidSpecificationException(
                sprintf('Response content-type %s is not currently supported.', json_encode($unsupportedContentTypes))
            );
        }

        $this->bodyContentTypes = $bodyContentTypes;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): ?Field
    {
        return $this->body;
    }

    public function getBodyContentTypes(): array
    {
        return $this->bodyContentTypes;
    }
}
