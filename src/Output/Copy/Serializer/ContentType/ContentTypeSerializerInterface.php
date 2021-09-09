<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use Psr\Http\Message\StreamInterface;

interface ContentTypeSerializerInterface
{
    const LITERAL_VALUE_KEY = '__literalResponseValue';

    public function encode(SerializableInterface $body): string;

    public function decode(StreamInterface $body): array;

    public function getMimeType(): string;
}
