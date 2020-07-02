<?php

namespace DoclerLabs\ApiClientGenerator\Naming;

use DoclerLabs\ApiClientBase\Exception\BadRequestResponseException;
use DoclerLabs\ApiClientBase\Exception\ForbiddenResponseException;
use DoclerLabs\ApiClientBase\Exception\NotFoundResponseException;
use DoclerLabs\ApiClientBase\Exception\UnauthorizedResponseException;
use UnexpectedValueException;

class ResponseExceptionNaming
{
    private const EXCEPTION_NAME_SUFFIX    = 'ResponseException';

    private const EXCEPTION_STATUS_MAPPING = [
        400 => 'BadRequest',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'NotFound',
    ];

    private const EXCEPTION_CLASS_MAPPING  = [
        400 => BadRequestResponseException::class,
        401 => UnauthorizedResponseException::class,
        403 => ForbiddenResponseException::class,
        404 => NotFoundResponseException::class,
    ];

    public static function getResponseExceptionName(int $statusCode): string
    {
        if (!array_key_exists($statusCode, static::EXCEPTION_STATUS_MAPPING)) {
            throw new UnexpectedValueException($statusCode . ' is not a known status code for Exception class.');
        }

        return sprintf('%s%s', static::EXCEPTION_STATUS_MAPPING[$statusCode], self::EXCEPTION_NAME_SUFFIX);
    }

    public static function getResponseExceptionImport(int $statusCode): string
    {
        if (!array_key_exists($statusCode, static::EXCEPTION_CLASS_MAPPING)) {
            throw new UnexpectedValueException($statusCode . ' is not a known status code for Exception class.');
        }

        return static::EXCEPTION_CLASS_MAPPING[$statusCode];
    }
}
