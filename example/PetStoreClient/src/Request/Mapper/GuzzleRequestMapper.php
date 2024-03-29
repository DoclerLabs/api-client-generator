<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace OpenApi\PetStoreClient\Request\Mapper;

use GuzzleHttp\Psr7\Request;
use OpenApi\PetStoreClient\Request\CookieJar;
use OpenApi\PetStoreClient\Request\RequestInterface;
use OpenApi\PetStoreClient\Serializer\BodySerializer;
use OpenApi\PetStoreClient\Serializer\QuerySerializer;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;

class GuzzleRequestMapper implements RequestMapperInterface
{
    private BodySerializer $bodySerializer;

    private QuerySerializer $querySerializer;

    public function __construct(BodySerializer $bodySerializer, QuerySerializer $querySerializer)
    {
        $this->bodySerializer  = $bodySerializer;
        $this->querySerializer = $querySerializer;
    }

    public function map(RequestInterface $request): PsrRequestInterface
    {
        $body        = $this->bodySerializer->serializeRequest($request);
        $query       = $this->querySerializer->serializeRequest($request);
        $psr7Request = new Request($request->getMethod(), $request->getRoute(), $request->getHeaders(), $body, '1.1');
        $psr7Request = $psr7Request->withUri($psr7Request->getUri()->withQuery($query));
        $cookieJar   = new CookieJar($request->getCookies());
        $psr7Request = $cookieJar->withCookieHeader($psr7Request);

        return $psr7Request;
    }
}
