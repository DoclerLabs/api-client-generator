<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Serializer;

use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use JsonSerializable;

class QuerySerializer
{
    public function serializeRequest(RequestInterface $request): string
    {
        $queryParameters = [];

        foreach ($request->getRawQueryParameters() as $name => $value) {
            if ($value === null) {
                continue;
            } elseif ($value instanceof SerializableInterface) {
                $value = $value->toArray();
            } elseif ($value instanceof JsonSerializable) {
                $value = $value->jsonSerialize();
            }

            $queryParameters[$name] = $value;
        }

        return http_build_query($queryParameters, '', '&', PHP_QUERY_RFC3986);
    }
}
