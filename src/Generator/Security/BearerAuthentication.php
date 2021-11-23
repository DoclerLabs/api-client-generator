<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use cebe\openapi\spec\SecurityRequirement;
use cebe\openapi\spec\SecurityScheme;
use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Generator\SecurityStrategyInterface;
use DoclerLabs\ApiClientGenerator\Input\Specification;

class BearerAuthentication implements SecurityStrategyInterface
{
    private const PROPERTY_NAME = 'bearerToken';
    private const SCHEME        = 'bearer';
    private const TYPE          = 'http';

    private CodeBuilder $builder;

    public function __construct(CodeBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function getProperties(Operation $operation, Specification $specification): array
    {
        $statements = [];

        if ($this->requiresBearerToken($operation, $specification)) {
            $statements[] = $this->builder->localProperty('bearerToken', 'string', 'string');
        }

        return $statements;
    }

    public function getConstructorParams(Operation $operation, Specification $specification): array
    {
        $params = [];

        if ($this->requiresBearerToken($operation, $specification)) {
            $params[] = $this->builder
                ->param(self::PROPERTY_NAME)
                ->setType('string')
                ->getNode();
        }

        return $params;
    }

    public function getConstructorParamInits(Operation $operation, Specification $specification): array
    {
        $paramInits = [];

        if ($this->requiresBearerToken($operation, $specification)) {
            $paramInits[] = $this->builder->assign(
                $this->builder->localPropertyFetch(self::PROPERTY_NAME),
                $this->builder->var(self::PROPERTY_NAME)
            );
        }

        return $paramInits;
    }

    public function getSecurityHeaders(Operation $operation, Specification $specification): array
    {
        $headers = [];

        foreach ($this->loopSecuritySchemes($operation, $specification) as $securityScheme) {
            /** @var SecurityScheme $securityScheme */
            if (
                $securityScheme->scheme === self::SCHEME
                && $securityScheme->type === self::TYPE
            ) {
                $headers['Authorization'] = $this->builder->funcCall(
                    'sprintf',
                    ['Bearer %s', $this->builder->localPropertyFetch(self::PROPERTY_NAME)]
                );
            }
        }

        return $headers;
    }

    private function requiresBearerToken(Operation $operation, Specification $specification): bool
    {
        foreach ($this->loopSecuritySchemes($operation, $specification) as $securityScheme) {
            /** @var SecurityScheme $securityScheme */
            if (
                $securityScheme->scheme === self::SCHEME
                && $securityScheme->type === self::TYPE
            ) {
                return true;
            }
        }

        return false;
    }

    private function loopSecuritySchemes(Operation $operation, Specification $specification): iterable
    {
        if (
            !empty($specification->getSecuritySchemes())
            && !empty($operation->getSecurity())
        ) {
            /** @var SecurityScheme $securityScheme */
            foreach ($specification->getSecuritySchemes() as $name => $securityScheme) {
                /** @var SecurityRequirement $securityRequirement */
                foreach ($operation->getSecurity() as $securityRequirement) {
                    if (isset($securityRequirement->$name)) {
                        yield $securityScheme;
                    }
                }
            }
        }

        yield from [];
    }
}
