<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use PhpParser\Node\Expr;

class BearerAuthenticationSecurityStrategy extends SecurityStrategyAbstract
{
    private const PROPERTY_NAME = 'bearerToken';
    private const SCHEME = 'bearer';
    private const TYPE = 'http';

    public function getProperties(Operation $operation, Specification $specification): array
    {
        if ($this->phpVersion->isConstructorPropertyPromotionSupported()) {
            return [];
        }

        $statements = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $statements[] = $this->builder->localProperty(self::PROPERTY_NAME, 'string', 'string');
        }

        return $statements;
    }

    public function getConstructorParams(Operation $operation, Specification $specification): array
    {
        $params = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $params[] = $this->builder
                ->param(self::PROPERTY_NAME)
                ->setType('string');
        }

        return $params;
    }

    public function getConstructorParamInits(Operation $operation, Specification $specification): array
    {
        if ($this->phpVersion->isConstructorPropertyPromotionSupported()) {
            return [];
        }

        $paramInits = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $paramInits[] = $this->builder->assign(
                $this->builder->localPropertyFetch(self::PROPERTY_NAME),
                $this->builder->var(self::PROPERTY_NAME)
            );
        }

        return $paramInits;
    }

    protected function getScheme(): string
    {
        return self::SCHEME;
    }

    protected function getType(): string
    {
        return self::TYPE;
    }

    protected function getAuthorizationHeader(): Expr
    {
        return $this->builder->funcCall(
            'sprintf',
            ['Bearer %s', $this->builder->localPropertyFetch(self::PROPERTY_NAME)]
        );
    }
}
