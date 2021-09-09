<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

class VdnApiJsonContentTypeSerializer extends AbstractJsonContentTypeSerializer
{
    public const MIME_TYPE = 'application/vnd.api+json';

    public function getMimeType(): string
    {
        return self::MIME_TYPE;
    }
}
