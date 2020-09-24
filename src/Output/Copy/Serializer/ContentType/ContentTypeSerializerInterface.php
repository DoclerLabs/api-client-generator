<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Response\Body\DecodedResponseBody;
use Psr\Http\Message\StreamInterface;

interface ContentTypeSerializerInterface
{
    public function encode(array $requestBody): StreamInterface;

    public function decode(StreamInterface $responseBody): DecodedResponseBody;
}