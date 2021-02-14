<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ClientNaming;
use DoclerLabs\ApiClientGenerator\Naming\CopiedNamespace;
use DoclerLabs\ApiClientGenerator\Naming\RequestNaming;
use DoclerLabs\ApiClientGenerator\Naming\SchemaMapperNaming;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\Mapper\RequestMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Response\ResponseHandler;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\ContentTypeSerializerInterface;
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
        $classBuilder = $this->builder
            ->class(ClientNaming::getClassName($specification))
            ->addStmts($this->generateProperties())
            ->addStmt($this->generateConstructor())
            ->addStmt($this->generateSendRequestMethod());

        foreach ($specification->getOperations() as $operation) {
            $classBuilder->addStmt($this->generateAction($operation));
        }

        $classBuilder->addStmt($this->generateHandleResponse());

        $this->registerFile($fileRegistry, $classBuilder);
    }

    protected function generateSendRequestMethod(): ClassMethod
    {
        $requestVariable = $this->builder->var('request');
        $methodParameter = $this->builder
            ->param('request')
            ->setType('RequestInterface')
            ->getNode();

        $mapMethodCall = $this->builder->methodCall(
            $this->builder->methodCall(
                $this->builder->localPropertyFetch('container'),
                'get',
                [$this->builder->classConstFetch('RequestMapperInterface', 'class')]
            ),
            'map',
            $this->builder->args([$requestVariable])
        );

        $clientCall = $this->builder->methodCall(
            $this->builder->localPropertyFetch('client'),
            'sendRequest',
            [$mapMethodCall]
        );

        return $this->builder
            ->method('sendRequest')
            ->makePublic()
            ->addParam($methodParameter)
            ->addStmt($this->builder->return($clientCall))
            ->setReturnType('ResponseInterface')
            ->composeDocBlock([$methodParameter], 'ResponseInterface')
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

        $responseStmt = $this->builder->localMethodCall('sendRequest', [$requestVar]);

        $handleResponseStmt = $this->builder->localMethodCall(
            'handleResponse',
            $this->builder->args([$responseStmt])
        );

        $responseBody = $operation->getSuccessfulResponse()->getBody();
        if ($responseBody === null) {
            return $this->builder
                ->method($operation->getName())
                ->makePublic()
                ->addParam($methodParam)
                ->addStmt($handleResponseStmt)
                ->setReturnType(null)
                ->composeDocBlock([$methodParam])
                ->getNode();
        }

        $responseVariable = $this->builder->var('response');
        $stmts[]          = $this->builder->assign($responseVariable, $handleResponseStmt);
        if ($responseBody->isComposite()) {
            $mapperClassName = SchemaMapperNaming::getClassName($responseBody);
            $this->addImport(
                sprintf(
                    '%s%s\\%s',
                    $this->baseNamespace,
                    SchemaMapperGenerator::NAMESPACE_SUBPATH,
                    $mapperClassName
                )
            );

            $getMethod = $this->builder->methodCall(
                $this->builder->localPropertyFetch('container'),
                'get',
                [$this->builder->classConstFetch($mapperClassName, 'class')]
            );

            $mapMethod = $this->builder->methodCall($getMethod, 'toSchema', [$responseVariable]);
            $stmts[]   = $this->builder->return($mapMethod);

            $this->addImport(
                sprintf(
                    '%s%s\\%s',
                    $this->baseNamespace,
                    SchemaGenerator::NAMESPACE_SUBPATH,
                    $responseBody->getPhpClassName()
                )
            );
        } else {
            $this->addImport(CopiedNamespace::getImport($this->baseNamespace, ContentTypeSerializerInterface::class));
            $literalValue = $this->builder->getArrayItem(
                $responseVariable,
                $this->builder->classConstFetch('ContentTypeSerializerInterface', 'LITERAL_VALUE_KEY')
            );

            $stmts[] = $this->builder->return($literalValue);
        }

        return $this->builder
            ->method($operation->getName())
            ->makePublic()
            ->addParam($methodParam)
            ->addStmts($stmts)
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
            $this->builder->localProperty('container', 'ContainerInterface', 'ContainerInterface'),
        ];
    }

    protected function generateConstructor(): ClassMethod
    {
        $this
            ->addImport(ClientInterface::class)
            ->addImport(ResponseInterface::class)
            ->addImport(ContainerInterface::class)
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, RequestMapperInterface::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, ResponseHandler::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, RequestInterface::class));

        $parameters[] = $this->builder
            ->param('client')
            ->setType('ClientInterface')
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

    public function generateHandleResponse(): ClassMethod
    {
        $parameters[] = $this->builder
            ->param('response')
            ->setType('ResponseInterface')
            ->getNode();
        $response     = $this->builder->var('response');

        $handleResponseStatement = $this->builder->return(
            $this->builder->methodCall(
                $this->builder->methodCall(
                    $this->builder->localPropertyFetch('container'),
                    'get',
                    [$this->builder->classConstFetch('ResponseHandler', 'class')]
                ),
                'handle',
                $this->builder->args([$response])
            )
        );

        return $this->builder
            ->method('handleResponse')
            ->makeProtected()
            ->addParams($parameters)
            ->addStmt($handleResponseStatement)
            ->composeDocBlock($parameters)
            ->getNode();
    }
}
