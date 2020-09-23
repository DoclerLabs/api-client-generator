<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClient;

use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClientImplementationInterface;
use GuzzleHttp\Client;

class Guzzle7HttpClient extends GuzzleHttpClientAbstract implements HttpClientImplementationInterface
{
    public function generateInitBaseClientMethod(): MethodBuilder
    {
        $statements       = [];
        $configVariable   = $this->builder->var('config');
        $configStatements = $this->getConfigStatements($configVariable);
        foreach ($configStatements as $configStatement) {
            $statements[] = $configStatement;
        }
        $client       = $this->builder->new('Client', $this->builder->args([$configVariable]));
        $statements[] = $this->builder->return($client);

        return $this->builder
            ->method('initBaseClient')
            ->addStmts($statements);
    }

    public function getInitBaseClientImports(): array
    {
        return [
            Client::class,
            'InvalidArgumentException',
        ];
    }

    public function getPackages(): array
    {
        return [
            'guzzle/guzzle' => '^7.1',
        ];
    }
}