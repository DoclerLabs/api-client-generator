<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\StaticPhp\Serializer;

use Psr\Http\Message\StreamInterface;

interface BodySerializerInterface
{
    public function match(string $contentType): bool;

    public function encode(array $requestBody): StreamInterface;

    public function decode(StreamInterface $responseBody): array;
}