<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClient\Guzzle6HttpClient;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClient\Guzzle7HttpClient;
use UnexpectedValueException;

class HttpClientImplementationStrategy implements HttpClientImplementationInterface
{
    public const  HTTP_CLIENT_GUZZLE6         = 'guzzle6';
    public const  HTTP_CLIENT_GUZZLE7         = 'guzzle7';
    private const HTTP_CLIENT_IMPLEMENTATIONS = [
        self::HTTP_CLIENT_GUZZLE6 => Guzzle6HttpClient::class,
        self::HTTP_CLIENT_GUZZLE7 => Guzzle7HttpClient::class,
    ];
    private HttpClientImplementationInterface $httpClientImplementation;

    public function __construct(string $httpClient, CodeBuilder $builder)
    {
        if (!isset(self::HTTP_CLIENT_IMPLEMENTATIONS[$httpClient])) {
            $supported = json_encode(self::HTTP_CLIENT_IMPLEMENTATIONS, JSON_THROW_ON_ERROR);

            throw new UnexpectedValueException(
                'Unsupported http client `' . $httpClient . '`. Should be one of ' . $supported
            );
        }
        $implementationClassName = self::HTTP_CLIENT_IMPLEMENTATIONS[$httpClient];

        $this->httpClientImplementation = new $implementationClassName($builder);
    }

    public function generateInitBaseClientMethod(): MethodBuilder
    {
        return $this->httpClientImplementation->generateInitBaseClientMethod();
    }

    public function getInitBaseClientImports(): array
    {
        return $this->httpClientImplementation->getInitBaseClientImports();
    }

    public function getPackages(): array
    {
        return $this->httpClientImplementation->getPackages();
    }
}
