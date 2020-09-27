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

    public function generateInitRequestMapperMethod(): MethodBuilder
    {
        return $this->builder
            ->method('initRequestMapper')
            ->addStmt(
                $this->builder->return(
                    $this->builder->new(
                        $this->getRequestMapperClassName(),
                        [$this->builder->new('BodySerializer')]
                    )
                )
            );
    }

    public function generateRequestMapMethod(): MethodBuilder
    {
        $arguments  = [];
        $statements = [];

        $requestVariable     = $this->builder->var('request');
        $psr7RequestVariable = $this->builder->var('psr7Request');

        $bodyVariable = $this->builder->var('body');

        $encodeMethodCall = $this->builder->methodCall(
            $this->builder->localPropertyFetch('serializer'),
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
        $arguments[] = $this->builder->array([]);

        $statements[] = $this->builder->assign(
            $psr7RequestVariable,
            $this->builder->new(
                'ServerRequest',
                $arguments
            )
        );
        $statements[] = $this->builder->methodCall(
            $psr7RequestVariable,
            'withQueryParams',
            [$this->builder->methodCall($requestVariable, 'getQueryParameters')]
        );
        $statements[] = $this->builder->methodCall(
            $psr7RequestVariable,
            'withCookieParams',
            [$this->builder->methodCall($requestVariable, 'getCookies')]
        );

        $statements[] = $this->builder->return($psr7RequestVariable);

        return $this->builder
            ->method('map')
            ->addStmts($statements);
    }

    abstract public function getRequestMapperClassName(): string;
}