<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\StaticPhp\Request\Mapper;

use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Exception\RequestValidationException;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Serializer\BodySerializerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class GuzzleRequestMapper implements RequestMapperInterface
{
    const HTTP_VERSION = "1.1";
    /** @var BodySerializerInterface */
    private $serializer;

    public function __construct(ServerRequestFactoryInterface $factory, BodySerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @throws RequestValidationException
     */
    public function map(RequestInterface $request): ServerRequestInterface
    {
        $serverRequest = new ServerRequest(
            $request->getMethod(),
            $request->getRoute(),
            $request->getHeaders(),
            $this->serializer->encode($request->getBody()),
            self::HTTP_VERSION,
            []
        );

        $serverRequest->withQueryParams($request->getQueryParameters());
        $serverRequest->withCookieParams($request->getCookies());

        return $serverRequest;
    }
}
