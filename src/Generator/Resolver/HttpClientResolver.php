<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Resolver;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use UnexpectedValueException;

class HttpClientResolver
{
    public const HTTP_CLIENT_GUZZLE6    = 'guzzle/guzzle:^6';
    public const HTTP_CLIENT_GUZZLE7    = 'guzzle/guzzle:^7';
    public const SUPPORTED_HTTP_CLIENTS = [
        self::HTTP_CLIENT_GUZZLE6,
        self::HTTP_CLIENT_GUZZLE7,
    ];
    private string      $httpClient;
    private CodeBuilder $builder;

    public function __construct(string $httpClient, CodeBuilder $builder)
    {
        if (!in_array($httpClient, self::SUPPORTED_HTTP_CLIENTS, true)) {
            $versions = json_encode(self::SUPPORTED_HTTP_CLIENTS, JSON_THROW_ON_ERROR);

            throw new UnexpectedValueException(
                'Unsupported http client ' . $httpClient . '. Should be one of ' . $versions
            );
        }

        $this->httpClient = $httpClient;
        $this->builder    = $builder;
    }
}
