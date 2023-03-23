<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use IteratorAggregate;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;

class RequestFieldRegistry implements IteratorAggregate
{
    public const  ORIGIN_QUERY    = 'query';
    public const  ORIGIN_PATH     = 'path';
    public const  ORIGIN_HEADER   = 'header';
    public const  ORIGIN_COOKIE   = 'cookie';
    public const  ORIGIN_BODY     = 'body';
    private const ALLOWED_ORIGINS = [
        self::ORIGIN_QUERY,
        self::ORIGIN_PATH,
        self::ORIGIN_HEADER,
        self::ORIGIN_COOKIE,
        self::ORIGIN_BODY,
    ];
    private array $items = [];

    public function add(string $type, Field $data): self
    {
        if (!in_array($type, self::ALLOWED_ORIGINS, true)) {
            throw new UnexpectedValueException('Request field origin is not supported: ' . $type);
        }

        $this->items[$type][] = $data;

        return $this;
    }

    public function getQueryFields(): array
    {
        if (!isset($this->items[self::ORIGIN_QUERY])) {
            return [];
        }

        return $this->items[self::ORIGIN_QUERY];
    }

    public function getPathFields(): array
    {
        if (!isset($this->items[self::ORIGIN_PATH])) {
            return [];
        }

        return $this->items[self::ORIGIN_PATH];
    }

    public function getHeaderFields(): array
    {
        if (!isset($this->items[self::ORIGIN_HEADER])) {
            return [];
        }

        return $this->items[self::ORIGIN_HEADER];
    }

    public function getCookieFields(): array
    {
        if (!isset($this->items[self::ORIGIN_COOKIE])) {
            return [];
        }

        return $this->items[self::ORIGIN_COOKIE];
    }

    public function getBody(): ?Field
    {
        if (!isset($this->items[self::ORIGIN_BODY])) {
            return null;
        }

        return current($this->items[self::ORIGIN_BODY]);
    }

    /**
     * @return RecursiveIteratorIterator|Field[]
     */
    public function getIterator(): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveArrayIterator($this->items, RecursiveArrayIterator::CHILD_ARRAYS_ONLY)
        );
    }
}
