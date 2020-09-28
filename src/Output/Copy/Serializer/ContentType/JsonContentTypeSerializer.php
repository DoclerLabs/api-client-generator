<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\Json\Json;
use Psr\Http\Message\StreamInterface;

class JsonContentTypeSerializer implements ContentTypeSerializerInterface
{
    const JSON_DEPTH   = 512;
    const JSON_OPTIONS = JSON_BIGINT_AS_STRING | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE;

    public function encode(SerializableInterface $body): string
    {
        return Json::encode($body->toArray(), self::JSON_OPTIONS);
    }

    public function decode(StreamInterface $body): array
    {
        $body->rewind();

        return Json::decode($body->getContents(), true, self::JSON_DEPTH, self::JSON_OPTIONS);
    }
}