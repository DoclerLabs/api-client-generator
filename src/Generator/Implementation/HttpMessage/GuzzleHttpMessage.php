<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessage;

use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementation;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationInterface;

class GuzzleHttpMessage extends HttpMessageAbstract implements HttpMessageImplementationInterface
{
    public function getRequestMapperClassName(): string
    {
        return ucfirst(HttpMessageImplementation::HTTP_MESSAGE_GUZZLE) . 'RequestMapper';
    }

    public function getPackages(): array
    {
        return [
            'guzzlehttp/psr7' => '^1.6',
        ];
    }
}