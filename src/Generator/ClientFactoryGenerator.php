<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClientImplementation;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ClientNaming;
use DoclerLabs\ApiClientGenerator\Naming\ResponseMapperNaming;
use DoclerLabs\ApiClientGenerator\Naming\StaticClassNamespace;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Request\Mapper\RequestMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Handler\ResponseHandler;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\ResponseMapperRegistry;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\ResponseMapperRegistryInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Serializer\BodySerializer;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Psr\Http\Client\ClientInterface;

class ClientFactoryGenerator extends GeneratorAbstract
{
    private HttpClientImplementation  $clientImplementation;
    private HttpMessageImplementation $messageImplementation;

    public function __construct(
        string $baseNamespace,
        CodeBuilder $builder,
        HttpClientImplementation $clientImplementation,
        HttpMessageImplementation $messageImplementation
    ) {
        parent::__construct($baseNamespace, $builder);
        $this->clientImplementation  = $clientImplementation;
        $this->messageImplementation = $messageImplementation;
    }

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $className = ClientNaming::getFactoryClassName($specification);

        $this
            ->addImport(ClientInterface::class)
            ->addImport(StaticClassNamespace::getImport($this->baseNamespace, RequestMapperInterface::class))
            ->addImport(StaticClassNamespace::getImport($this->baseNamespace, ResponseHandler::class))
            ->addImport(StaticClassNamespace::getImport($this->baseNamespace, ResponseMapperRegistry::class))
            ->addImport(StaticClassNamespace::getImport($this->baseNamespace, ResponseMapperRegistryInterface::class))
            ->addImport(StaticClassNamespace::getImport($this->baseNamespace, BodySerializer::class))
            ->addImport(
                sprintf(
                    '%s%s\\%s',
                    $this->baseNamespace,
                    RequestMapperGenerator::NAMESPACE_SUBPATH,
                    $this->messageImplementation->getRequestMapperClassName()
                )
            );

        foreach ($this->clientImplementation->getInitBaseClientImports() as $import) {
            $this->addImport($import);
        }

        $initBaseClientMethodParams   = [];
        $initBaseClientMethodParams[] = $this->builder
            ->param('baseUri')
            ->setType('string')
            ->getNode();
        $initBaseClientMethodParams[] = $this->builder
            ->param('options')
            ->setType('array')
            ->getNode();

        $initBaseClientMethod = $this->clientImplementation
            ->generateInitBaseClientMethod()
            ->makePrivate()
            ->addParams($initBaseClientMethodParams)
            ->setReturnType('ClientInterface')
            ->getNode();

        $initRequestMapperMethod = $this->messageImplementation
            ->generateInitRequestMapperMethod()
            ->makePrivate()
            ->setReturnType('RequestMapperInterface')
            ->getNode();

        $classBuilder = $this->builder
            ->class($className)
            ->addStmt($this->generateCreate($specification))
            ->addStmt($this->generateRegisterResponseMappers($specification))
            ->addStmt($initBaseClientMethod)
            ->addStmt($initRequestMapperMethod);

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

        $registryVar  = $this->builder->var('registry');
        $statements[] = $this->builder->assign(
            $registryVar,
            $this->builder->new('ResponseMapperRegistry')
        );

        $statements[]    = $this->builder->localMethodCall('registerResponseMappers', [$registryVar]);
        $clientClassName = ClientNaming::getClassName($specification);
        $statements[]    = $this->builder->return(
            $this->builder->new(
                $clientClassName,
                $this->builder->args(
                    [
                        $this->builder->localMethodCall(
                            'initBaseClient',
                            [$this->builder->var('baseUrl'), $this->builder->var('options')]
                        ),
                        $this->builder->localMethodCall('initRequestMapper'),
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
            /** @var Field $field */
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

            $mapperClassConst = $this->builder->classConstFetch($mapperClass, 'class');

            $closureStatements[] = $this->builder->return($this->buildMapperDependencies($field, $registryVar));

            $closure = $this->builder->closure($closureStatements, [], [$registryVar], $mapperClass);

            $statements[] = $this->builder->methodCall($registryVar, 'add', [$mapperClassConst, $closure]);
        }

        return $this->builder
            ->method('registerResponseMappers')
            ->addParam($param)
            ->addStmts($statements)
            ->setReturnType(null)
            ->composeDocBlock([$param], '', [])
            ->getNode();
    }

    private function buildMapperDependencies(Field $field, Variable $registryVar): New_
    {
        $dependencies = [];
        if ($field->isObject()) {
            $alreadyInjected = [];
            foreach ($field->getObjectProperties() as $subfield) {
                if ($subfield->isComposite() && !isset($alreadyInjected[$subfield->getPhpClassName()])) {
                    $getMethodArg   =
                        $this->builder->classConstFetch(ResponseMapperNaming::getClassName($subfield), 'class');
                    $dependencies[] = $this->builder->methodCall($registryVar, 'get', [$getMethodArg]);

                    $alreadyInjected[$subfield->getPhpClassName()] = true;
                }
            }
        }
        if ($field->isArrayOfObjects()) {
            $getMethodArg   =
                $this->builder->classConstFetch(ResponseMapperNaming::getClassName($field->getArrayItem()), 'class');
            $dependencies[] = $this->builder->methodCall($registryVar, 'get', [$getMethodArg]);
        }

        return $this->builder->new(ResponseMapperNaming::getClassName($field), $dependencies);
    }
}
