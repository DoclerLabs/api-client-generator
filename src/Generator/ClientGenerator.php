<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Ast\ParameterNode;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Entity\Response;
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
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

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

        $sendRequestStmt = $this->builder->localMethodCall('sendRequest', [$this->builder->var('request')]);
        $methodParam     = $this->builder->param('request')->setType($requestClassName)->getNode();
        $responses       = $operation->getSuccessfulResponses();
        if (count($responses) === 1) {
            if ($responses[0]->getBody() === null) {
                return $this->emptyBodyAction($operation, $sendRequestStmt, $methodParam);
            }

            return $this->singleBodyAction($operation, $responses[0]->getBody(), $sendRequestStmt, $methodParam);
        }

        return $this->multiBodyAction($operation, $responses, $sendRequestStmt, $methodParam);
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

    private function generateHandleResponse(): ClassMethod
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

    private function processResponse(Variable $unserializedResponseVar, Field $responseBody): array
    {
        $stmts = [];
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

            $mapMethod = $this->builder->methodCall($getMethod, 'toSchema', [$unserializedResponseVar]);
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
                $unserializedResponseVar,
                $this->builder->classConstFetch('ContentTypeSerializerInterface', 'LITERAL_VALUE_KEY')
            );

            $stmts[] = $this->builder->return($literalValue);
        }

        return $stmts;
    }

    private function emptyBodyAction(
        Operation $operation,
        MethodCall $sendRequestStmt,
        ParameterNode $methodParam
    ): ClassMethod {
        return $this
            ->builder
            ->method($operation->getName())
            ->makePublic()
            ->addParam($methodParam)
            ->addStmt($this->builder->localMethodCall('handleResponse', [$sendRequestStmt]))
            ->setReturnType(MethodBuilder::RETURN_TYPE_VOID)
            ->composeDocBlock([$methodParam])
            ->getNode();
    }

    private function singleBodyAction(
        Operation $operation,
        Field $responseBody,
        MethodCall $sendRequestStmt,
        ParameterNode $methodParam
    ): ClassMethod {
        $responseVar        = $this->builder->var('response');
        $handleResponseStmt = $this->builder->localMethodCall('handleResponse', $this->builder->args([$sendRequestStmt]));
        $stmts              = [
            $this->builder->assign($responseVar, $handleResponseStmt),
            ...$this->processResponse($responseVar, $responseBody)
        ];

        return $this
            ->builder
            ->method($operation->getName())
            ->makePublic()
            ->addParam($methodParam)
            ->addStmts($stmts)
            ->setReturnType($responseBody->getPhpTypeHint(), $responseBody->isNullable())
            ->composeDocBlock([$methodParam], $responseBody->getPhpDocType(false))
            ->getNode();
    }

    private function multiBodyAction(
        Operation $operation,
        array $responses,
        MethodCall $sendRequestStmt,
        ParameterNode $methodParam
    ): ClassMethod {
        $responseVar             = $this->builder->var('response');
        $stmts                   = [$this->builder->assign($responseVar, $sendRequestStmt)];
        $handleResponseStmt      = $this->builder->localMethodCall('handleResponse', [$responseVar]);
        $unserializedResponseVar = $this->builder->var('unserializedResponse');

        $stmts[] = $this->builder->assign($unserializedResponseVar, $handleResponseStmt);

        $caseConditions  = [];
        $caseBodies      = [];
        $returnTypeHints = [];
        $isNullable      = false;
        $nullableCases   = [];
        foreach ($responses as $response) {
            /** @var Response $response */
            $responseBody = $response->getBody();
            if ($responseBody === null) {
                $isNullable = true;

                $nullableCases[$response->getStatusCode()] = $this->builder->return($this->builder->val(null));
            } else {
                $returnTypeHints[$responseBody->getPhpTypeHint()] = true;
                $isNullable = $isNullable || $responseBody->isNullable();

                $phpClassName = $responseBody->getPhpClassName();

                $caseConditions[$phpClassName][] = new LNumber($response->getStatusCode());
                if (!isset($caseBodies[$phpClassName])) {
                    $caseBodies[$phpClassName] = $this->processResponse($unserializedResponseVar, $responseBody);
                }
            }
        }

        $cases = [];
        foreach ($nullableCases as $statusCode => $nullableCase) {
            $cases[] = $this->builder->case(new LNumber($statusCode), $nullableCase);
        }
        foreach ($caseBodies as $phpClassName => $caseBody) {
            /** @var Stmt[] $caseBody */
            for ($i = 0, $l = count($caseConditions[$phpClassName]) - 1; $i < $l; ++$i) {
                $cases[] = $this->builder->case($caseConditions[$phpClassName][$i]);
            }
            $cases[] = $this->builder->case($caseConditions[$phpClassName][$l], ...$caseBody);
        }

        $stmts[] = $this->builder->switch(
            $this->builder->methodCall($responseVar, 'getStatusCode'),
            ...$cases
        );

        $this->addImport(RuntimeException::class);
        $stmts[] = $this->builder->throw(
            'RuntimeException',
            $this->builder->val('Response status code not properly mapped in schema.')
        );

        $method = $this
            ->builder
            ->method($operation->getName())
            ->makePublic()
            ->addParam($methodParam)
            ->addStmts($stmts);

        if (count($returnTypeHints) === 1) {
            $returnTypeHint = array_keys($returnTypeHints)[0];
            $method->setReturnType($returnTypeHint, $isNullable);
        }

        return $method->getNode();
    }
}
