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
use DoclerLabs\ApiClientGenerator\Output\Copy\Response\ErrorHandler;
use DoclerLabs\ApiClientGenerator\Output\Copy\Response\Mapper\ResponseMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use InvalidArgumentException;
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
            ->addStmt($this->generateConstructor());

        foreach ($specification->getOperations() as $operation) {
            $classBuilder->addStmt($this->generateAction($operation));
        }

        $classBuilder
            ->addStmt($this->generateSendRequestMethod())
            ->addStmt($this->generateGetResponseMapperMethod());

        $this->registerFile($fileRegistry, $classBuilder);
    }

    protected function generateSendRequestMethod(): ClassMethod
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

        return $this->builder->method('sendRequest')
            ->makePublic()
            ->addParam($methodParam)
            ->addStmt($this->builder->return($clientCall))
            ->setReturnType('ResponseInterface')
            ->composeDocBlock([$methodParam], 'ResponseInterface')
            ->getNode();
    }

    protected function generateGetResponseMapperMethod(): ClassMethod
    {
        $this
            ->addImport(InvalidArgumentException::class)
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, ResponseMapperInterface::class));

        $statements       = [];
        $mapperClassParam = $this->builder
            ->param('mapperClass')
            ->setType('string')
            ->getNode();

        $mapperClassVariable = $this->builder->var('mapperClass');

        $ifCondition = $this->builder->not(
            $this->builder->methodCall(
                $this->builder->localPropertyFetch('container'),
                'has',
                [$mapperClassVariable]
            )
        );

        $statements[] = $this->builder->if(
            $ifCondition,
            [
                $this->builder->throw(
                    InvalidArgumentException::class,
                    $this->builder->concat(
                        $this->builder->val('Response mapper not found: '),
                        $mapperClassVariable
                    )
                ),
            ]
        );
        $statements[] = $this->builder->return(
            $this->builder->methodCall(
                $this->builder->localPropertyFetch('container'),
                'get',
                [$mapperClassVariable]
            )
        );

        return $this->builder->method('getResponseMapper')
            ->makePublic()
            ->addParam($mapperClassParam)
            ->addStmts($statements)
            ->setReturnType('ResponseMapperInterface')
            ->composeDocBlock([$mapperClassParam], 'ResponseMapperInterface')
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

        $errorHandledStmt = $this->builder->methodCall(
            $this->builder->localPropertyFetch('errorHandler'),
            'handle',
            $this->builder->args([$responseStmt])
        );

        $responseBody = $operation->getSuccessfulResponse()->getBody();
        if ($responseBody === null) {
            return $this->builder->method($operation->getName())
                ->makePublic()
                ->addParam($methodParam)
                ->addStmt($errorHandledStmt)
                ->setReturnType(null)
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

        $getMethod = $this->builder->localMethodCall(
            'getResponseMapper',
            [$this->builder->classConstFetch($mapperClassName, 'class')]
        );

        $mapMethod  = $this->builder->methodCall($getMethod, 'map', [$errorHandledStmt]);
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
