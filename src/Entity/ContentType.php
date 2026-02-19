<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\VdnApiJsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\XmlContentTypeSerializer;

final class ContentType
{
    private const ALLOWED_CONTENT_TYPES = [
        JsonContentTypeSerializer::MIME_TYPE,
        FormUrlencodedContentTypeSerializer::MIME_TYPE,
        XmlContentTypeSerializer::MIME_TYPE,
        VdnApiJsonContentTypeSerializer::MIME_TYPE,
    ];

    private const JSON_SUFFIX = '+json';

    /**
     * Checks if a content type is supported by the generator.
     *
     * Supports:
     * - Standard content types (application/json, application/xml, etc.)
     * - RFC 6839 structured syntax suffixes (+json indicates JSON format)
     * - Content types with parameters (charset, version, etc.)
     */
    public static function isSupported(string $contentType): bool
    {
        $normalizedContentType = self::normalize($contentType);

        if (in_array($normalizedContentType, self::ALLOWED_CONTENT_TYPES, true)) {
            return true;
        }

        // RFC 6839: +json suffix indicates JSON-based format
        return str_ends_with($normalizedContentType, self::JSON_SUFFIX);
    }

    /**
     * Normalizes a content type by removing parameters and converting to lowercase.
     *
     * Example: "Application/JSON; charset=utf-8" becomes "application/json"
     */
    public static function normalize(string $contentType): string
    {
        return strtolower(trim(explode(';', $contentType)[0]));
    }

    /**
     * Checks if a content type is JSON-based (either standard or +json suffix).
     */
    public static function isJsonBased(string $contentType): bool
    {
        $normalizedContentType = self::normalize($contentType);

        return $normalizedContentType === JsonContentTypeSerializer::MIME_TYPE
               || str_ends_with($normalizedContentType, self::JSON_SUFFIX);
    }

    /**
     * Filters an array of content types and returns only unsupported ones.
     *
     * @param string[] $contentTypes
     *
     * @return string[]
     */
    public static function filterUnsupported(array $contentTypes): array
    {
        return array_values(
            array_filter(
                $contentTypes,
                static fn (string $contentType): bool => !self::isSupported($contentType)
            )
        );
    }
}
