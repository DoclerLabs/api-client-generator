<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Resolver;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use UnexpectedValueException;

class HttpMessageResolver
{
    public const HTTP_MESSAGE_GUZZLE     = 'guzzlehttp/psr7:^1';
    public const HTTP_MESSAGE_NYHOLM     = 'nyholm/psr7:^1';
    public const HTTP_MESSAGE_SLIM       = 'slim/psr7:^1';
    public const SUPPORTED_HTTP_MESSAGES = [
        self::HTTP_MESSAGE_GUZZLE,
        self::HTTP_MESSAGE_NYHOLM,
        self::HTTP_MESSAGE_SLIM,
    ];
    private string      $httpMessage;
    private CodeBuilder $codeBuilder;

    public function __construct(string $httpMessage, CodeBuilder $codeBuilder)
    {
        if (!in_array($httpMessage, self::SUPPORTED_HTTP_MESSAGES, true)) {
            $versions = json_encode(self::SUPPORTED_HTTP_MESSAGES, JSON_THROW_ON_ERROR);

            throw new UnexpectedValueException(
                'Unsupported http message ' . $httpMessage . '. Should be one of ' . $versions
            );
        }

        $this->httpMessage = $httpMessage;
        $this->codeBuilder = $codeBuilder;
    }
}
