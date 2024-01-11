<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;

abstract class ApiKeyBasedSecurityStrategyAbstract extends SecurityStrategyAbstract
{
    protected const PROPERTY_API_KEY = 'apiKey';

    public function getType(): string
    {
        return 'apiKey';
    }

    public function getProperties(Operation $operation, Specification $specification): array
    {
        if ($this->phpVersion->isConstructorPropertyPromotionSupported()) {
            return [];
        }

        $statements = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $statements[] = $this->builder->localProperty(
                self::PROPERTY_API_KEY,
                'string',
                'string'
            );
        }

        return $statements;
    }

    public function getConstructorParams(Operation $operation, Specification $specification): array
    {
        $params = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $params[] = $this->builder
                ->param(self::PROPERTY_API_KEY)
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
                $this->builder->localPropertyFetch(self::PROPERTY_API_KEY),
                $this->builder->var(self::PROPERTY_API_KEY)
            );
        }

        return $paramInits;
    }
}
