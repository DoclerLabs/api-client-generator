<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy;

use Psr\Http\Client\ClientInterface;

interface BaseClientFactoryInterface
{
    public function getClient(string $baseUri, array $options = []): ClientInterface;
}