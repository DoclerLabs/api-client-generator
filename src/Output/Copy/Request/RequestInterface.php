<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Request;

interface RequestInterface
{
    public function getContentType(): string;

    public function getMethod(): string;

    public function getRoute(): string;

    public function getCookies(): array;

    public function getHeaders(): array;

    public function getQueryParameters(): array;

    public function getBody();
}