<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test\Request\Mapper;

use Nyholm\Psr7\Request;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Test\Request\CookieJar;
use Test\Request\RequestInterface;
use Test\Serializer\BodySerializer;
use Test\Serializer\QuerySerializer;

class NyholmRequestMapper implements RequestMapperInterface
{
    public function __construct(private readonly BodySerializer $bodySerializer, private readonly QuerySerializer $querySerializer)
    {
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
