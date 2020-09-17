<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Generator\Resolver\HttpClientResolver;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ClientNaming;
use DoclerLabs\ApiClientGenerator\Naming\ResponseMapperNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Handler\ResponseHandler;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\ResponseMapperRegistry;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\ResponseMapperRegistryInterface;
use InvalidArgumentException;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use Psr\Http\Client\ClientInterface;

class ClientFactoryGenerator extends GeneratorAbstract
{
    public function __construct(string $baseNamespace, CodeBuilder $builder, HttpClientResolver $clientResolver)
    {
        parent::__construct($baseNamespace, $builder);
    }

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $className = ClientNaming::getFactoryClassName($specification);

        $this
            ->addImport(ClientInterface::class)
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

        $statements[] = $this->generateBaseUriValidation($baseUri);

        $defaultItems = [
            'base_uri'    => $baseUri,
            'timeout'     => $this->builder->val(3),
            'http_errors' => $this->builder->val(false),
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
