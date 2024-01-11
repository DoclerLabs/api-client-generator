<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use cebe\openapi\spec\SecurityScheme;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;

class XWSSESecurityStrategy extends ApiKeyInHeaderSecurityStrategy
{
    public const HEADER_NAME = 'X-WSSE';

    private const PROPERTY_USERNAME = 'xwsseUsername';

    private const PROPERTY_SECRET = 'xwsseSecret';

    public function getProperties(Operation $operation, Specification $specification): array
    {
        if ($this->phpVersion->isConstructorPropertyPromotionSupported()) {
            return [];
        }

        $statements = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $statements[] = $this->builder->localProperty(
                self::PROPERTY_USERNAME,
                'string',
                'string'
            );
            $statements[] = $this->builder->localProperty(
                self::PROPERTY_SECRET,
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
                ->param(self::PROPERTY_USERNAME)
                ->setType('string');
            $params[] = $this->builder
                ->param(self::PROPERTY_SECRET)
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
                $this->builder->localPropertyFetch(self::PROPERTY_USERNAME),
                $this->builder->var(self::PROPERTY_USERNAME)
            );
            $paramInits[] = $this->builder->assign(
                $this->builder->localPropertyFetch(self::PROPERTY_SECRET),
                $this->builder->var(self::PROPERTY_SECRET)
            );
        }

        return $paramInits;
    }

    public function getSecurityHeadersStmts(Operation $operation, Specification $specification): array
    {
        $stmts = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $nonce       = $this->builder->var('nonce');
            $nonceAssign = $this->builder->assign(
                $nonce,
                $this->builder->funcCall('bin2hex', [$this->builder->funcCall('random_bytes', [16])])
            );

            $timestamp       = $this->builder->var('timestamp');
            $timestampAssign = $this->builder->assign($timestamp, $this->builder->funcCall('gmdate', ['c']));

            $xwsse       = $this->builder->var('xwsse');
            $xwsseAssign = $this->builder->assign(
                $xwsse,
                $this->builder->funcCall(
                    'sprintf',
                    [
                        'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
                        $this->builder->localPropertyFetch(self::PROPERTY_USERNAME),
                        $this->builder->funcCall(
                            'base64_encode',
                            [
                                $this->builder->funcCall(
                                    'sha1',
                                    [
                                        $this->builder->concat(
                                            $nonce,
                                            $timestamp,
                                            $this->builder->localPropertyFetch(self::PROPERTY_SECRET),
                                        ),
                                    ]
                                ),
                            ]
                        ),
                        $nonce,
                        $timestamp,
                    ]
                )
            );

            array_push($stmts, $nonceAssign, $timestampAssign, $xwsseAssign);
        }

        return $stmts;
    }

    public function getSecurityHeaders(Operation $operation, Specification $specification): array
    {
        $headers = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $headers[self::HEADER_NAME] = $this->builder->var('xwsse');
        }

        return $headers;
    }

    protected function matches(SecurityScheme $securityScheme): bool
    {
        return parent::matches($securityScheme)
            && $securityScheme->name === self::HEADER_NAME;
    }
}
