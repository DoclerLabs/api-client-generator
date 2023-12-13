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

    public function __construct(
        public readonly int $statusCode,
        public readonly ?Field $body = null,
        public readonly array $bodyContentTypes = []
    ) {
        $unsupportedContentTypes = array_diff($bodyContentTypes, self::ALLOWED_CONTENT_TYPES);
        if (!empty($unsupportedContentTypes)) {
            throw new InvalidSpecificationException(
                sprintf('Response content-type %s is not currently supported.', json_encode($unsupportedContentTypes))
            );
        }
    }
}
