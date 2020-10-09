<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessage;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;

abstract class HttpMessageAbstract
{
    protected CodeBuilder $builder;

    public function __construct(CodeBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function generateRequestMapMethod(): MethodBuilder
    {
        $arguments  = [];
        $statements = [];

        $requestVariable     = $this->builder->var('request');
        $psr7RequestVariable = $this->builder->var('psr7Request');
        $query               = $this->builder->var('query');
        $cookieJar           = $this->builder->var('cookieJar');

        $bodyVariable = $this->builder->var('body');

        $encodeMethodCall = $this->builder->methodCall(
            $this->builder->localPropertyFetch('bodySerializer'),
            'serializeRequest',
            [
                $requestVariable,
            ]
        );

        $statements[] = $this->builder->assign($bodyVariable, $encodeMethodCall);

        $arguments[] = $this->builder->methodCall($requestVariable, 'getMethod');
        $arguments[] = $this->builder->methodCall($requestVariable, 'getRoute');
        $arguments[] = $this->builder->methodCall($requestVariable, 'getHeaders');
        $arguments[] = $bodyVariable;
        $arguments[] = $this->builder->val('1.1');

        $statements[] = $this->builder->assign(
            $query,
            $this->builder->funcCall(
                'http_build_query',
                [
                    $this->builder->methodCall($requestVariable, 'getQueryParameters'),
                    '',
                    '&',
                    $this->builder->constFetch('PHP_QUERY_RFC3986')
                ]
            )
        );
        $statements[] = $this->builder->assign(
            $psr7RequestVariable,
            $this->builder->new(
                'Request',
                $arguments
            )
        );
        $statements[] = $this->builder->assign(
            $psr7RequestVariable,
            $this->builder->methodCall(
                $psr7RequestVariable,
                'withUri',
                [
                    $this->builder->methodCall(
                        $this->builder->methodCall($psr7RequestVariable, 'getUri'),
                        'withQuery',
                        [$query]
                    )
                ]
            )
        );
        $statements[] = $this->builder->assign(
            $cookieJar,
            $this->builder->new(
                'CookieJar',
                [true, $this->builder->methodCall($requestVariable, 'getCookies')]
            )
        );
        $statements[] = $this->builder->assign(
            $psr7RequestVariable,
            $this->builder->methodCall(
                $cookieJar,
                'withCookieHeader',
                [$psr7RequestVariable]
            )
        );

        $statements[] = $this->builder->return($psr7RequestVariable);

        return $this->builder
            ->method('map')
            ->addStmts($statements);
    }

    abstract public function getRequestMapperClassName(): string;
}
