<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessage;

use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationInterface;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use GuzzleHttp\Psr7\Request;

class GuzzleHttpMessage extends HttpMessageAbstract implements HttpMessageImplementationInterface
{
    public function getRequestMapperClassName(): string
    {
        return ucfirst(HttpMessageImplementationStrategy::HTTP_MESSAGE_GUZZLE) . 'RequestMapper';
    }

    public function getPackages(): array
    {
        return [
            'guzzlehttp/psr7' => '^1.6 || ^2.6',
        ];
    }

    public function getInitMessageImports(): array
    {
        return [
            Request::class,
        ];
    }
}
