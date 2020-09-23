<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\StaticPhp\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\DecodedResponseBody;
use Psr\Http\Message\StreamInterface;

interface ContentTypeSerializerInterface
{
    public function encode(array $requestBody): StreamInterface;

    public function decode(StreamInterface $responseBody): DecodedResponseBody;
}