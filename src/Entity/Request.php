<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\VdnApiJsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\XmlContentTypeSerializer;

class Request
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const PATCH = 'PATCH';
    public const OPTIONS = 'OPTIONS';
    public const DELETE = 'DELETE';
    public const HEAD = 'HEAD';
    public const ALLOWED_METHODS = [
        self::GET,
        self::POST,
        self::PUT,
        self::PATCH,
        self::OPTIONS,
        self::DELETE,
        self::HEAD,
    ];
    private const ALLOWED_CONTENT_TYPES = [
        JsonContentTypeSerializer::MIME_TYPE,
        FormUrlencodedContentTypeSerializer::MIME_TYPE,
        XmlContentTypeSerializer::MIME_TYPE,
        VdnApiJsonContentTypeSerializer::MIME_TYPE,
    ];

    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly RequestFieldRegistry $fields,
        public readonly array $bodyContentTypes
    ) {
        if (!in_array($method, self::ALLOWED_METHODS, true)) {
            throw new InvalidSpecificationException(
                sprintf('Unsupported request method `%s` in `%s`.', $method, $path)
            );
        }

        $unsupportedContentTypes = array_diff($bodyContentTypes, Request::ALLOWED_CONTENT_TYPES);
        if (!empty($unsupportedContentTypes)) {
            throw new InvalidSpecificationException(
                sprintf('Request content-type %s is not currently supported.', json_encode($unsupportedContentTypes))
            );
        }
    }
}
