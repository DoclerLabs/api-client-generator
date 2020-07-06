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
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use PhpParser\Node\Expr\New_;
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
            ->param('options')
            ->setType('array')
            ->setDefault($this->builder->val([]))
            ->getNode();

        $default = $this->builder->var('default');
        $config  = $this->builder->var('config');
        $baseUri = $this->builder->var('baseUri');
        $options = $this->builder->var('options');
        $server  = $this->builder->var('_SERVER');

        $statements[] = $this->generateBaseUriValidation($baseUri);

        $defaultItems = [
            'base_uri'                  => $baseUri,
            RequestOptions::TIMEOUT     => $this->builder->val(3),
            RequestOptions::HEADERS     => $this->builder->array(
                [
                    'Accept'       => $this->builder->val('application/json'),
                    'Content-Type' => $this->builder->val('application/json'),
                    'X-Client-Ip'  => $this->builder->coalesce(
                        $this->builder->getArrayItem($server, $this->builder->val('HTTP_X_CLIENT_IP')),
                        $this->builder->coalesce(
                            $this->builder->getArrayItem($server, $this->builder->val('REMOTE_ADDR')),
                            $this->builder->val(null)
                        )
                    ),
                ]
            ),
            RequestOptions::HTTP_ERRORS => $this->builder->val(false),
        ];

        $statements[] = $this->builder->assign($default, $this->builder->array($defaultItems));

        $statements[] = $this->builder->assign(
            $config,
            $this->builder->funcCall('array_replace_recursive', [$default, $options])
        );

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

    private function buildMapperDependencies(Field $field): New_
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
