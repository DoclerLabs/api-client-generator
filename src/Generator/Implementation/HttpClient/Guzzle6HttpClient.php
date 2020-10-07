<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClient;

use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClientImplementationInterface;
use GuzzleHttp\Client;

class Guzzle6HttpClient extends GuzzleHttpClientAbstract implements HttpClientImplementationInterface
{
    public function generateInitBaseClientMethod(): MethodBuilder
    {
        $statements       = [];
        $configVariable   = $this->builder->var('config');
        $configStatements = $this->getConfigStatements($configVariable);
        foreach ($configStatements as $configStatement) {
            $statements[] = $configStatement;
        }
        $client        = $this->builder->new('Client', $this->builder->args([$configVariable]));
        $clientAdapter = $this->builder->new('ClientAdapter', $this->builder->args([$client]));
        $statements[]  = $this->builder->return($clientAdapter);

        return $this->builder
            ->method('initBaseClient')
            ->addStmts($statements);
    }

    public function getInitBaseClientImports(): array
    {
        return [
            Client::class,
            'Http\\Adapter\\Guzzle6\\Client as ClientAdapter',
            'InvalidArgumentException',
        ];
    }

    public function getPackages(): array
    {
        return [
            'guzzlehttp/guzzle'        => '^6.5',
            'php-http/guzzle6-adapter' => '^2.0',
        ];
    }
}