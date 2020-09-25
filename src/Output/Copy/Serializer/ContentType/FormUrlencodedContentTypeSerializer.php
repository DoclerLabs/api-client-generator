<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FormUrlencodedContentTypeSerializer implements ContentTypeSerializerInterface
{
    public function encodeBody(RequestInterface $request): string
    {
        if ($request->getBody() === null) {
            return '';
        }

        return http_build_query($request->getBody()->toArray());
    }

    public function decodeBody(ResponseInterface $response): array
    {
        $body = $response->getBody();
        $body->rewind();
        parse_str($body->getContents(), $decoded);

        return $decoded;
    }
}