<?php

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientBase\Request\Mapper\RequestMapperInterface;
use DoclerLabs\ApiClientBase\Response\Handler\ResponseHandlerInterface;
use DoclerLabs\ApiClientBase\Response\ResponseMapperRegistryInterface;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ClientNaming;
use DoclerLabs\ApiClientGenerator\Naming\RequestNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use GuzzleHttp\ClientInterface;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

class ClientGenerator extends GeneratorAbstract
{
    private string $baseNamespace;

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $methods             = [];
        $this->baseNamespace = $fileRegistry->getBaseNamespace();
        foreach ($specification->getOperations() as $operation) {
            $methods[] = $this->generateAction($operation);
        }

        $classBuilder = $this->builder
            ->class(ClientNaming::getClassName($specification))
            ->addStmts($this->generateProperties())
            ->addStmt($this->generateConstructor())
            ->addStmts($methods);

        $this->registerFile($fileRegistry, $classBuilder);
    }

    protected function generateAction(Operation $operation): ClassMethod
    {
        $requestClassName = RequestNaming::getClassName($operation);
        $this->addImport(
            sprintf(
                '%s%s\\%s',
                $this->baseNamespace,
                RequestGenerator::NAMESPACE_SUBPATH,
                $requestClassName
            )
        );

        $methodParam = $this->builder
            ->param('request')
            ->setType($requestClassName)
            ->getNode();

        $requestVar = $this->builder->var('request');
        $args       = [
            $this->builder->methodCall($requestVar, 'getMethod'),
            $this->builder->methodCall($requestVar, 'getRoute'),
            $this->builder->methodCall(
                $this->builder->localPropertyFetch('requestHandler'),
                'getParameters',
                $this->builder->args([$requestVar])
            ),
        ];

        $clientCall = $this->builder->methodCall($this->builder->localPropertyFetch('client'), 'request', $args);

        $responseStmt = $this->builder->methodCall(
            $this->builder->localPropertyFetch('responseHandler'),
            'handle',
            $this->builder->args([$clientCall])
        );

        $responseBody = $operation->getSuccessfulResponse()->getBody();
        if ($responseBody !== null) {
            $responseClassName = $responseBody->getPhpClassName();
            $responseVariable  = $this->builder->var('response');
            $responseStmt      = $this->builder->assign($responseVariable, $responseStmt);

            $getMethodArg = $this->builder->classConstFetch($responseClassName, 'class');
            $getMethod    = $this->builder->methodCall(
                $this->builder->localPropertyFetch('mapperRegistry'),
                'get',
                [$getMethodArg]
            );

            $mapMethod  = $this->builder->methodCall($getMethod, 'map', [$responseVariable]);
            $returnStmt = $this->builder->return($mapMethod);

            $this->addImport(
                sprintf(
                    '%s%s\\%s',
                    $this->baseNamespace,
                    SchemaGenerator::NAMESPACE_SUBPATH,
                    $responseBody->getPhpClassName()
                )
            );

            return $this->builder->method($operation->getName())
                ->makePublic()
                ->addParam($methodParam)
                ->addStmt($responseStmt)
                ->addStmt($returnStmt)
                ->setReturnType($responseBody->getPhpTypeHint())
                ->composeDocBlock([$methodParam], $responseBody->getPhpDocType(false))
                ->getNode();
        }

        return $this->builder->method($operation->getName())
            ->makePublic()
            ->addParam($methodParam)
            ->addStmt($responseStmt)
            ->composeDocBlock([$methodParam])
            ->getNode();
    }

    /**
     * @return Property[]
     */
    protected function generateProperties(): array
    {
        return [
            $this->builder->localProperty('client', 'ClientInterface'),
            $this->builder->localProperty('requestHandler', 'RequestMapperInterface'),
            $this->builder->localProperty('responseHandler', 'ResponseHandlerInterface'),
            $this->builder->localProperty('mapperRegistry', 'ResponseMapperRegistryInterface'),
        ];
    }

    protected function generateConstructor(): ClassMethod
    {
        $this
            ->addImport(ClientInterface::class)
            ->addImport(ResponseHandlerInterface::class)
            ->addImport(RequestMapperInterface::class)
            ->addImport(ResponseHandlerInterface::class)
            ->addImport(ResponseMapperRegistryInterface::class);

        $parameters[] = $this->builder
            ->param('client')
            ->setType('ClientInterface')
            ->getNode();
        $inits[]      = $this->builder->assign(
            $this->builder->localPropertyFetch('client'),
            $this->builder->var('client')
        );

        $parameters[] = $this->builder
            ->param('requestHandler')
            ->setType('RequestMapperInterface')
            ->getNode();
        $inits[]      = $this->builder->assign(
            $this->builder->localPropertyFetch('requestHandler'),
            $this->builder->var('requestHandler')
        );

        $parameters[] = $this->builder
            ->param('responseHandler')
            ->setType('ResponseHandlerInterface')
            ->getNode();
        $inits[]      = $this->builder->assign(
            $this->builder->localPropertyFetch('responseHandler'),
            $this->builder->var('responseHandler')
        );

        $parameters[] = $this->builder
            ->param('mapperRegistry')
            ->setType('ResponseMapperRegistryInterface')
            ->getNode();
        $inits[]      = $this->builder->assign(
            $this->builder->localPropertyFetch('mapperRegistry'),
            $this->builder->var('mapperRegistry')
        );

        return $this->builder
            ->method('__construct')
            ->makePublic()
            ->addParams($parameters)
            ->addStmts($inits)
            ->composeDocBlock($parameters)
            ->getNode();
    }
}
