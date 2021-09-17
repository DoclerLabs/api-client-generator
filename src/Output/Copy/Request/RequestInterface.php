<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Request;

use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;

interface RequestInterface
{
    public function getContentType(): string;

    public function getMethod(): string;

    public function getRoute(): string;

    public function getCookies(): array;

    public function getHeaders(): array;

    public function getQueryParameters(): array;

    public function getRawQueryParameters(): array;

    /**
     * @return SerializableInterface|null
     */
    public function getBody();
}
