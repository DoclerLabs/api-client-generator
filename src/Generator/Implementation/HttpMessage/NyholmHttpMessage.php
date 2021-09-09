<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessage;

use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationInterface;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use Nyholm\Psr7\Request;

class NyholmHttpMessage extends HttpMessageAbstract implements HttpMessageImplementationInterface
{
    public function getRequestMapperClassName(): string
    {
        return ucfirst(HttpMessageImplementationStrategy::HTTP_MESSAGE_NYHOLM) . 'RequestMapper';
    }

    public function getPackages(): array
    {
        return [
            'nyholm/psr7' => '^1.3',
        ];
    }

    public function getInitMessageImports(): array
    {
        return [
            Request::class,
        ];
    }
}
