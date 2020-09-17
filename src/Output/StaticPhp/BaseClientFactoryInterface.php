<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\StaticPhp;

use Psr\Http\Client\ClientInterface;

interface BaseClientFactoryInterface
{
    public function getClient(string $baseUri, array $options = []): ClientInterface;
}