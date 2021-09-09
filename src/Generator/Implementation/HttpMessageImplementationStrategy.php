<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessage\GuzzleHttpMessage;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessage\NyholmHttpMessage;
use UnexpectedValueException;

class HttpMessageImplementationStrategy implements HttpMessageImplementationInterface
{
    public const HTTP_MESSAGE_GUZZLE          = 'guzzle';
    public const HTTP_MESSAGE_NYHOLM          = 'nyholm';
    public const HTTP_MESSAGE_IMPLEMENTATIONS = [
        self::HTTP_MESSAGE_GUZZLE => GuzzleHttpMessage::class,
        self::HTTP_MESSAGE_NYHOLM => NyholmHttpMessage::class,
    ];
    private HttpMessageImplementationInterface $httpMessageImplementation;

    public function __construct(string $httpMessage, CodeBuilder $builder)
    {
        if (!isset(self::HTTP_MESSAGE_IMPLEMENTATIONS[$httpMessage])) {
            $supported = json_encode(self::HTTP_MESSAGE_IMPLEMENTATIONS, JSON_THROW_ON_ERROR);

            throw new UnexpectedValueException(
                'Unsupported http message `' . $httpMessage . '`. Should be one of ' . $supported
            );
        }
        $implementationClassName = self::HTTP_MESSAGE_IMPLEMENTATIONS[$httpMessage];

        $this->httpMessageImplementation = new $implementationClassName($builder);
    }

    public function generateRequestMapMethod(): MethodBuilder
    {
        return $this->httpMessageImplementation->generateRequestMapMethod();
    }

    public function getInitMessageImports(): array
    {
        return $this->httpMessageImplementation->getInitMessageImports();
    }

    public function getPackages(): array
    {
        return $this->httpMessageImplementation->getPackages();
    }

    public function getRequestMapperClassName(): string
    {
        return $this->httpMessageImplementation->getRequestMapperClassName();
    }
}
