<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use Psr\Http\Message\StreamInterface;

class FormUrlencodedContentTypeSerializer implements ContentTypeSerializerInterface
{
    const MIME_TYPE = 'application/x-www-form-urlencoded';

    public function encode(SerializableInterface $body): string
    {
        return http_build_query($body->toArray());
    }

    public function decode(StreamInterface $body): array
    {
        $body->rewind();
        parse_str($body->getContents(), $decoded);

        return $decoded;
    }

    public function getMimeType(): string
    {
        return self::MIME_TYPE;
    }
}
