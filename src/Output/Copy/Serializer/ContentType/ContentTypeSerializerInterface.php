<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Request\SerializableRequestBodyInterface;
use Psr\Http\Message\StreamInterface;

interface ContentTypeSerializerInterface
{
    public function encode(SerializableRequestBodyInterface $body): string;

    public function decode(StreamInterface $body): array;
}