<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ClientNaming;
use DoclerLabs\ApiClientGenerator\Naming\RequestNaming;
use DoclerLabs\ApiClientGenerator\Naming\ResponseMapperNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Request\Mapper\RequestMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Request\RequestInterface as ClientRequestInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Handler\ResponseHandlerInterface;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class ClientGenerator extends GeneratorAbstract
{
    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $methods = [$this->generateResponseAction()];
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

    protected function generateResponseAction(): ClassMethod
    {
        $requestVar  = $this->builder->var('request');
        $methodParam = $this->builder
            ->param('request')
            ->setType('RequestInterface')
            ->getNode();
        $args        = [
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

        return $this->builder->method('getResponse')
            ->makePublic()
            ->addParam($methodParam)
            ->addStmt($this->builder->return($responseStmt))
            ->setReturnType('Response')
            ->composeDocBlock([$methodParam], 'Response')
            ->getNode();
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

        $requestVar  = $this->builder->var('request');
        $methodParam = $this->builder
            ->param('request')
            ->setType($requestClassName)
            ->getNode();

        $responseStmt = $this->builder->localMethodCall('getResponse', [$requestVar]);

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
            ->setReturnType($responseBody->getPhpTypeHint(), $responseBody->isNullable())
            ->composeDocBlock([$methodParam], $responseBody->getPhpDocType(false))
            ->getNode();
    }

    /**
     * @return Property[]
     */
    protected function generateProperties(): array
    {
        return [
            $this->builder->localProperty('client', 'ClientInterface', 'ClientInterface'),
            $this->builder->localProperty('requestHandler', 'RequestMapperInterface', 'RequestMapperInterface'),
            $this->builder->localProperty('responseHandler', 'ResponseHandlerInterface', 'ResponseHandlerInterface'),
            $this->builder->localProperty(
                'mapperRegistry',
                'ResponseMapperRegistryInterface',
                'ResponseMapperRegistryInterface'
            ),
        ];
    }

    protected function generateConstructor(): ClassMethod
    {
        $this
            ->addImport(ClientInterface::class)
            ->addImport(ResponseInterface::class)
            ->addImport(ResponseHandlerInterface::class)
            ->addImport(RequestMapperInterface::class)
            ->addImport(ClientRequestInterface::class, 'ClientRequestInterface')
            ->addImport(ResponseHandlerInterface::class)
            ->addImport(ContainerInterface::class);

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
            ->setType('ContainerInterface')
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
