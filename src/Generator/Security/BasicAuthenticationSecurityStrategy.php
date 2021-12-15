<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Entity\ImportCollection;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\CopiedNamespace;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\AuthenticationCredentials;
use PhpParser\Node\Expr;

class BasicAuthenticationSecurityStrategy extends SecurityStrategyAbstract
{
    public const SCHEME                = 'basic';
    private const PROPERTY_CREDENTIALS = 'credentials';
    private const TYPE                 = 'http';

    private CodeBuilder $builder;

    public function __construct(CodeBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function getImports(string $baseNamespace): ImportCollection
    {
        return parent::getImports($baseNamespace)
            ->add(CopiedNamespace::getImport($baseNamespace, AuthenticationCredentials::class));
    }

    public function getProperties(Operation $operation, Specification $specification): array
    {
        $statements = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $statements[] = $this->builder->localProperty(
                self::PROPERTY_CREDENTIALS,
                'AuthenticationCredentials',
                'AuthenticationCredentials'
            );
        }

        return $statements;
    }

    public function getConstructorParams(Operation $operation, Specification $specification): array
    {
        $params = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $params[] = $this->builder
                ->param(self::PROPERTY_CREDENTIALS)
                ->setType('AuthenticationCredentials')
                ->getNode();
        }

        return $params;
    }

    public function getConstructorParamInits(Operation $operation, Specification $specification): array
    {
        $paramInits = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $paramInits[] = $this->builder->assign(
                $this->builder->localPropertyFetch(self::PROPERTY_CREDENTIALS),
                $this->builder->var(self::PROPERTY_CREDENTIALS)
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
            [
                'Basic %s',
                $this->builder->funcCall(
                    'base64_encode',
                    [
                        $this->builder->funcCall(
                            'sprintf',
                            [
                                '%s:%s',
                                $this->builder->methodCall(
                                    $this->builder->localPropertyFetch(self::PROPERTY_CREDENTIALS),
                                    'getUsername'
                                ),
                                $this->builder->methodCall(
                                    $this->builder->localPropertyFetch(self::PROPERTY_CREDENTIALS),
                                    'getPassword'
                                ),
                            ]
                        ),
                    ]
                ),
            ]
        );
    }
}
