<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ContentTypeSerializerInterface
{
    public function encodeBody(RequestInterface $request): string;

    public function decodeBody(ResponseInterface $response): array;
}