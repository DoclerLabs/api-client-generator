<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientBase\Request\Mapper\RequestMapperInterface;
use DoclerLabs\ApiClientBase\Response\Response;
use DoclerLabs\ApiClientBase\Response\Handler\ResponseHandlerInterface;
use DoclerLabs\ApiClientBase\Response\ResponseMapperRegistryInterface;
use DoclerLabs\ApiClientGenerator\Builder\ParameterNode;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ClientNaming;
use DoclerLabs\ApiClientGenerator\Naming\RequestNaming;
use DoclerLabs\ApiClientGenerator\Naming\ResponseMapperNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use GuzzleHttp\ClientInterface;
use PhpParser\Node\Expr\Variable;
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
            [$action, $responseAction] = $this->generateActions($operation);
            $methods[] = $action;
            $methods[] = $responseAction;
        }

        $classBuilder = $this->builder
            ->class(ClientNaming::getClassName($specification))
            ->addStmts($this->generateProperties())
            ->addStmt($this->generateConstructor())
            ->addStmts($methods);

        $this->registerFile($fileRegistry, $classBuilder);
    }

    /**
     * @param Operation $operation
     *
     * @return ClassMethod[]
     */
    protected function generateActions(Operation $operation): array
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

        $requestVar  = $this->builder->var('request');
        $methodParam = $this->builder
            ->param('request')
            ->setType($requestClassName)
            ->getNode();

        return [
            $this->generateAction($operation, $methodParam, $requestVar),
            $this->generateResponseAction($operation, $methodParam, $requestVar),
        ];
    }

    protected function generateResponseAction(Operation $operation, ParameterNode $methodParam, Variable $requestVar): ClassMethod
    {
        $args = [
            $this->builder->methodCall($requestVar, 'getMethod'),
            $this->builder->methodCall($requestVar, 'getRoute'),
            $this->builder->methodCall(
                $this->builder->localPropertyFetch('requestHandler'),
                'getParameters',
                $this->builder->args([$requestVar])
            ),
        ];

        $clientCall   = $this->builder->methodCall($this->builder->localPropertyFetch('client'), 'request', $args);
        $responseStmt = $this->builder->methodCall(
            $this->builder->localPropertyFetch('responseHandler'),
            'handle',
            $this->builder->args([$clientCall])
        );

        return $this->builder->method($operation->getName() . 'Response')
            ->makePublic()
            ->addParam($methodParam)
            ->addStmt($this->builder->return($responseStmt))
            ->setReturnType('Response')
            ->composeDocBlock([$methodParam], 'Response')
            ->getNode();
    }

    protected function generateAction(Operation $operation, ParameterNode $methodParam, Variable $requestVar): ClassMethod
    {
        $responseStmt = $this->builder->localMethodCall($operation->getName() . 'Response', [$requestVar]);

        $responseBody = $operation->getSuccessfulResponse()->getBody();
        if ($responseBody === null) {
            return $this->builder->method($operation->getName())
                ->makePublic()
                ->addParam($methodParam)
                ->addStmt($responseStmt)
                ->composeDocBlock([$methodParam])
                ->getNode();
        }

        $mapperClassName = ResponseMapperNaming::getClassName($responseBody);
        $this->addImport(
            sprintf(
                '%s%s\\%s',
                $this->baseNamespace,
                ResponseMapperGenerator::NAMESPACE_SUBPATH,
                $mapperClassName
            )
        );

        $getMethod = $this->builder->methodCall(
            $this->builder->localPropertyFetch('mapperRegistry'),
            'get',
            [$this->builder->classConstFetch($mapperClassName, 'class')]
        );

        $mapMethod  = $this->builder->methodCall($getMethod, 'map', [$responseStmt]);
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
            ->addStmt($returnStmt)
            ->setReturnType($responseBody->getPhpTypeHint())
            ->composeDocBlock([$methodParam], $responseBody->getPhpDocType(false))
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
            ->addImport(Response::class)
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
