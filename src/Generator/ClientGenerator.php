<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ClientNaming;
use DoclerLabs\ApiClientGenerator\Naming\CopiedNamespace;
use DoclerLabs\ApiClientGenerator\Naming\RequestNaming;
use DoclerLabs\ApiClientGenerator\Naming\ResponseMapperNaming;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\Mapper\RequestMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Response\Handler\ErrorHandler;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
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

        $mapMethodCall = $this->builder->methodCall(
            $this->builder->localPropertyFetch('requestMapper'),
            'map',
            $this->builder->args([$requestVar])
        );
        $clientCall    = $this->builder->methodCall(
            $this->builder->localPropertyFetch('client'),
            'sendRequest',
            [$mapMethodCall]
        );
        $responseStmt  = $this->builder->methodCall(
            $this->builder->localPropertyFetch('errorHandler'),
            'handle',
            $this->builder->args([$clientCall])
        );

        return $this->builder->method('getResponse')
            ->makePublic()
            ->addParam($methodParam)
            ->addStmt($this->builder->return($responseStmt))
            ->setReturnType('ResponseInterface')
            ->composeDocBlock([$methodParam], 'ResponseInterface')
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
            $this->builder->localPropertyFetch('container'),
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
            $this->builder->localProperty('requestMapper', 'RequestMapperInterface', 'RequestMapperInterface'),
            $this->builder->localProperty('errorHandler', 'ErrorHandler', 'ErrorHandler'),
            $this->builder->localProperty(
                'container',
                'ContainerInterface',
                'ContainerInterface'
            ),
        ];
    }

    protected function generateConstructor(): ClassMethod
    {
        $this
            ->addImport(ClientInterface::class)
            ->addImport(ResponseInterface::class)
            ->addImport(ContainerInterface::class)
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, RequestMapperInterface::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, ErrorHandler::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, RequestInterface::class));

        $parameters[] = $this->builder
            ->param('client')
            ->setType('ClientInterface')
            ->getNode();
        $parameters[] = $this->builder
            ->param('requestMapper')
            ->setType('RequestMapperInterface')
            ->getNode();
        $parameters[] = $this->builder
            ->param('errorHandler')
            ->setType('ErrorHandler')
            ->getNode();
        $parameters[] = $this->builder
            ->param('container')
            ->setType('ContainerInterface')
            ->getNode();

        $inits[] = $this->builder->assign(
            $this->builder->localPropertyFetch('client'),
            $this->builder->var('client')
        );
        $inits[] = $this->builder->assign(
            $this->builder->localPropertyFetch('requestMapper'),
            $this->builder->var('requestMapper')
        );
        $inits[] = $this->builder->assign(
            $this->builder->localPropertyFetch('errorHandler'),
            $this->builder->var('errorHandler')
        );
        $inits[] = $this->builder->assign(
            $this->builder->localPropertyFetch('container'),
            $this->builder->var('container')
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
