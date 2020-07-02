<?php

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientBase\Request\Mapper\RequestMapper;
use DoclerLabs\ApiClientBase\Response\Handler\ResponseHandler;
use DoclerLabs\ApiClientBase\Response\ResponseMapperRegistry;
use DoclerLabs\ApiClientBase\Response\ResponseMapperRegistryInterface;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ClientNaming;
use DoclerLabs\ApiClientGenerator\Naming\ResponseMapperNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;

class ClientFactoryGenerator extends GeneratorAbstract
{
    private string $baseNamespace;

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $className           = ClientNaming::getFactoryClassName($specification);
        $this->baseNamespace = $fileRegistry->getBaseNamespace();

        $this
            ->addImport(Client::class)
            ->addImport(HandlerStack::class)
            ->addImport(ResponseHandler::class)
            ->addImport(RequestMapper::class)
            ->addImport(ResponseMapperRegistry::class)
            ->addImport(ResponseMapperRegistryInterface::class);

        $classBuilder = $this->builder
            ->class($className)
            ->addStmt($this->generateCreate($specification))
            ->addStmt($this->generateRegisterResponseMappers($specification));

        $this->registerFile($fileRegistry, $classBuilder);
    }

    protected function generateCreate(Specification $specification): ClassMethod
    {
        $params   = [];
        $params[] = $this->builder
            ->param('baseUri')
            ->setType('string')
            ->getNode();
        $params[] = $this->builder
            ->param('connectionTimeout')
            ->setType('float')
            ->getNode();
        $params[] = $this->builder
            ->param('requestTimeout')
            ->setType('float')
            ->getNode();
        $params[] = $this->builder
            ->param('handlerStack')
            ->setType('HandlerStack')
            ->setDefault($this->builder->val(null))
            ->getNode();
        $params[] = $this->builder
            ->param('proxy')
            ->setType('string')
            ->setDefault($this->builder->val(null))
            ->getNode();

        $config = $this->builder->var('config');

        $baseUri      = $this->builder->var('baseUri');
        $statements[] = $this->generateBaseUriValidation($baseUri);

        $configItems  = [
            'base_uri'                      => $baseUri,
            'handler'                       => $this->builder->var('handlerStack'),
            RequestOptions::TIMEOUT         => $this->builder->var('requestTimeout'),
            RequestOptions::CONNECT_TIMEOUT => $this->builder->var('connectionTimeout'),
            RequestOptions::PROXY           => $this->builder->var('proxy'),
            RequestOptions::HTTP_ERRORS     => $this->builder->val(false),
            RequestOptions::HEADERS         => $this->builder->array(
                [
                    'Accept'       => $this->builder->val('application/json'),
                    'Content-Type' => $this->builder->val('application/json'),
                ]
            ),
        ];
        $statements[] = $this->builder->assign($config, $this->builder->array($configItems));

        $registryVar  = $this->builder->var('registry');
        $statements[] = $this->builder->assign(
            $registryVar,
            $this->builder->new('ResponseMapperRegistry')
        );

        $statements[] = $this->builder->localMethodCall('registerResponseMappers', [$registryVar]);

        $clientClassName = ClientNaming::getClassName($specification);
        $statements[]    = $this->builder->return(
            $this->builder->new(
                $clientClassName,
                $this->builder->args(
                    [
                        $this->builder->new('Client', $this->builder->args([$config])),
                        $this->builder->new('RequestMapper'),
                        $this->builder->new('ResponseHandler'),
                        $registryVar,
                    ]
                )
            )
        );

        return $this->builder
            ->method('create')
            ->addParams($params)
            ->addStmts($statements)
            ->setReturnType($clientClassName)
            ->composeDocBlock($params, $clientClassName)
            ->getNode();
    }

    protected function generateRegisterResponseMappers(Specification $specification): ClassMethod
    {
        $statements = [];

        $param = $this->builder
            ->param('registry')
            ->setType('ResponseMapperRegistryInterface')
            ->getNode();

        $registryVar     = $this->builder->var('registry');
        $compositeFields = $specification->getCompositeResponseFields()->getUniqueByPhpClassName();
        foreach ($compositeFields as $field) {
            $closureStatements = [];
            $mapperClass       = ResponseMapperNaming::getClassName($field);
            $this->addImport(
                sprintf(
                    '%s%s\\%s',
                    $this->baseNamespace,
                    ResponseMapperGenerator::NAMESPACE_SUBPATH,
                    $mapperClass
                )
            );
            $mapperClassConst = $this->builder->classConstFetch(
                $mapperClass,
                ResponseMapperGenerator::SCHEMA_CONST_NAME
            );

            $closureStatements[] = $this->builder->return($this->buildMapperDependencies($field));

            $closure = $this->builder->closure($closureStatements, [], [], $mapperClass);

            $statements[] = $this->builder->methodCall($registryVar, 'add', [$mapperClassConst, $closure]);
        }

        return $this->builder
            ->method('registerResponseMappers')
            ->addParam($param)
            ->addStmts($statements)
            ->composeDocBlock([$param], '', [], true)
            ->getNode();
    }

    private function buildMapperDependencies(Field $field)
    {
        $dependencies = [];
        if ($field->isObject()) {
            $alreadyInjected = [];
            foreach ($field->getAllProperties() as $subfield) {
                if ($subfield->isComposite() && !isset($alreadyInjected[$subfield->getPhpClassName()])) {
                    $dependencies[] = $this->buildMapperDependencies($subfield);

                    $alreadyInjected[$subfield->getPhpClassName()] = true;
                }
            }
        }
        if ($field->isArrayOfObjects()) {
            $dependencies[] = $this->buildMapperDependencies($field->getStructure()->getArrayItem());
        }

        return $this->builder->new(ResponseMapperNaming::getClassName($field), $dependencies);
    }

    private function generateBaseUriValidation(Variable $baseUri): Stmt
    {
        $lastCharacterFunction = $this->builder->funcCall('substr', [$baseUri, $this->builder->val(-1)]);
        $conditionStatement    = $this->builder->notEquals($lastCharacterFunction, $this->builder->val('/'));

        $this->addImport(InvalidArgumentException::class);

        $exceptionMessage = 'Base URI should end with the `/` symbol.';
        $throwStatement   = $this->builder->throw(
            'InvalidArgumentException',
            $this->builder->val($exceptionMessage)
        );

        return $this->builder->if($conditionStatement, [$throwStatement]);
    }
}
