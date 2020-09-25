<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Request\SerializableRequestBodyInterface;
use Psr\Http\Message\StreamInterface;

class FormUrlencodedContentTypeSerializer implements ContentTypeSerializerInterface
{
    public function encode(SerializableRequestBodyInterface $body): string
    {
        return http_build_query($body->toArray());
    }

    public function decode(StreamInterface $body): array
    {
        $body->rewind();
        parse_str($body->getContents(), $decoded);

        return $decoded;
    }
}