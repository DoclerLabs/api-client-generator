<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\ParameterNode;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Entity\Request;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\CopiedNamespace;
use DoclerLabs\ApiClientGenerator\Naming\RequestNaming;
use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\ClassMethod;

class RequestGenerator extends MutatorAccessorClassGeneratorAbstract
{
    public const NAMESPACE_SUBPATH = '\\Request';
    public const SUBDIRECTORY = 'Request/';

    /** @var SecurityStrategyInterface[] */
    private array $securityStrategies;

    public function __construct(
        string $baseNamespace,
        CodeBuilder $builder,
        SecurityStrategyInterface ...$securityStrategies
    ) {
        parent::__construct($baseNamespace, $builder);

        $this->securityStrategies = $securityStrategies;
    }

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        foreach ($specification->getOperations() as $operation) {
            $this->generateRequest($fileRegistry, $operation, $specification);
        }
    }

    protected function generateRequest(
        PhpFileCollection $fileRegistry,
        Operation $operation,
        Specification $specification
    ): void {
        $className = RequestNaming::getClassName($operation);
        $request   = $operation->getRequest();

        $classBuilder = $this->builder
            ->class($className)
            ->implement('RequestInterface')
            ->addStmts($this->generateEnums($request))
            ->addStmts($this->generateProperties($request, $operation, $specification))
            ->addStmt($this->generateConstructor($request, $operation, $specification))
            ->addStmt($this->generateGetContentType())
            ->addStmts($this->generateSetters($request))
            ->addStmt($this->generateGetMethod($request))
            ->addStmt($this->generateGetRoute($request))
            ->addStmts($this->generateGetParametersMethods($request, $operation, $specification));

        $this->registerFile($fileRegistry, $classBuilder, self::SUBDIRECTORY, self::NAMESPACE_SUBPATH);
    }

    protected function generateEnums(Request $request): array
    {
        $statements = [];
        foreach ($request->getFields() as $fields) {
            foreach ($fields as $field) {
                foreach ($this->generateEnumStatements($field) as $statement) {
                    $statements[] = $statement;
                }
            }
        }

        return $statements;
    }

    protected function generateProperties(Request $request, Operation $operation, Specification $specification): array
    {
        $statements = [];
        foreach ($request->getFields() as $fields) {
            foreach ($fields as $field) {
                if ($field->isComposite()) {
                    $this->addImport(
                        sprintf(
                            '%s%s\\%s',
                            $this->baseNamespace,
                            SchemaGenerator::NAMESPACE_SUBPATH,
                            $field->getPhpClassName()
                        )
                    );
                }
                $statements[] = $this->generateProperty($field);
            }
        }

        $default = null;
        if (count($request->getBodyContentTypes()) < 2) {
            $default = $this->builder->val($request->getBodyContentTypes()[0] ?? '');
        }
        $statements[] = $this->builder->localProperty('contentType', 'string', 'string', false, $default);

        foreach ($this->securityStrategies as $securityStrategy) {
            /** @var SecurityStrategyInterface $securityStrategy */
            array_push($statements, ...$securityStrategy->getProperties($operation, $specification));
        }

        return $statements;
    }

    protected function generateConstructor(
        Request $request,
        Operation $operation,
        Specification $specification
    ): ?ClassMethod {
        $params     = [];
        $paramInits = [];
        foreach ($request->getFields() as $fields) {
            foreach ($fields as $field) {
                if ($field->isRequired()) {
                    array_push($paramInits, ...$this->generateValidationStmts($field));

                    $param = $this->builder
                        ->param($field->getPhpVariableName())
                        ->setType($field->getPhpTypeHint(), $field->isNullable());

                    if (null !== $field->getDefault()) {
                        $param->setDefault($field->getDefault());
                    }

                    $params[] = $param->getNode();

                    $paramInits[] = $this->builder->assign(
                        $this->builder->localPropertyFetch($field->getPhpVariableName()),
                        $this->builder->var($field->getPhpVariableName())
                    );
                }
            }
        }

        foreach ($this->securityStrategies as $securityStrategy) {
            /** @var SecurityStrategyInterface $securityStrategy */
            array_push($params, ...$securityStrategy->getConstructorParams($operation, $specification));
            array_push($paramInits, ...$securityStrategy->getConstructorParamInits($operation, $specification));
        }

        if (count($request->getBodyContentTypes()) > 1) {
            $contentTypeVariableName = 'contentType';

            $params[] = $this->builder
                ->param($contentTypeVariableName)
                ->setType('string')
                ->getNode();

            $paramInits[] = $this->builder->assign(
                $this->builder->localPropertyFetch($contentTypeVariableName),
                $this->builder->var($contentTypeVariableName)
            );
        }

        if (empty($params)) {
            return null;
        }

        $params = $this->sortParameters(...$params);

        return $this->builder
            ->method('__construct')
            ->makePublic()
            ->addParams($params)
            ->addStmts($paramInits)
            ->composeDocBlock($params)
            ->getNode();
    }

    private function generateSetters(Request $request): array
    {
        $statements = [];
        foreach ($request->getFields() as $fields) {
            foreach ($fields as $field) {
                if (!$field->isRequired()) {
                    $statements[] = $this->generateSet($field);
                }
            }
        }

        return $statements;
    }

    private function generateGetContentType(): ClassMethod
    {
        $return     = $this->builder->return($this->builder->localPropertyFetch('contentType'));
        $returnType = 'string';

        return $this->builder
            ->method('getContentType')
            ->makePublic()
            ->addStmt($return)
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
    }

    private function generateGetMethod(Request $request): ClassMethod
    {
        $return     = $this->builder->return($this->builder->val($request->getMethod()));
        $returnType = 'string';

        return $this->builder
            ->method('getMethod')
            ->makePublic()
            ->addStmt($return)
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
    }

    private function generateGetRoute(Request $request): ClassMethod
    {
        $values     = [];
        $returnType = 'string';

        foreach ($request->getFields()->getPathFields() as $field) {
            $key          = sprintf('{%s}', $field->getName());
            $values[$key] = $this->builder->localPropertyFetch($field->getPhpVariableName());
        }

        if (empty($values)) {
            $return = $this->builder->return($this->builder->val($request->getPath()));

            return $this->builder
                ->method('getRoute')
                ->makePublic()
                ->addStmt($return)
                ->setReturnType($returnType)
                ->composeDocBlock([], $returnType)
                ->getNode();
        }

        $map    = $this->builder->array($values);
        $return = $this->builder->return(
            $this->builder->funcCall('strtr', [$this->builder->val($request->getPath()), $map])
        );

        return $this->builder
            ->method('getRoute')
            ->makePublic()
            ->addStmt($return)
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
    }

    private function generateGetParametersMethods(
        Request $request,
        Operation $operation,
        Specification $specification
    ): array {
        $methods   = [];
        $fields    = $request->getFields();
        $methods[] = $this->generateGetParametersMethod(
            'getQueryParameters',
            $fields->getQueryFields()
        );
        $methods[] = $this->generateGetRawParametersMethod(
            'getRawQueryParameters',
            $fields->getQueryFields()
        );
        $methods[] = $this->generateGetParametersMethod(
            'getCookies',
            $fields->getCookieFields()
        );
        $methods[] = $this->generateGetHeadersMethod($request, $fields->getHeaderFields(), $operation, $specification);
        $methods[] = $this->generateGetBody($fields->getBody());

        return $methods;
    }

    private function generateGetParametersMethod(string $methodName, array $fields): ClassMethod
    {
        $returnVal  = $this->builder->array([]);
        $fieldsArr  = [];
        $returnType = 'array';
        foreach ($fields as $field) {
            $fieldsArr[$field->getName()] = $this->builder->localPropertyFetch($field->getPhpVariableName());
        }

        if (!empty($fieldsArr)) {
            $returnVal = $this->generateParametersFromFields($fieldsArr);
        }

        return $this->builder
            ->method($methodName)
            ->makePublic()
            ->addStmt($this->builder->return($returnVal))
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
    }

    private function generateGetRawParametersMethod(string $methodName, array $fields): ClassMethod
    {
        $fieldsArr  = [];
        $returnType = 'array';
        foreach ($fields as $field) {
            $fieldsArr[$field->getName()] = $this->builder->localPropertyFetch($field->getPhpVariableName());
        }

        return $this->builder
            ->method($methodName)
            ->makePublic()
            ->addStmt($this->builder->return($this->builder->array($fieldsArr)))
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
    }

    private function generateGetBody(?Field $body): ClassMethod
    {
        if ($body !== null) {
            $returnType = $body->getPhpTypeHint();

            return $this->builder
                ->method('getBody')
                ->makePublic()
                ->addStmt($this->builder->return($this->builder->localPropertyFetch($body->getPhpVariableName())))
                ->composeDocBlock([], $returnType)
                ->getNode();
        }

        return $this->builder
            ->method('getBody')
            ->makePublic()
            ->addStmt($this->builder->return($this->builder->val(null)))
            ->getNode();
    }

    private function generateGetHeadersMethod(
        Request $request,
        array $fields,
        Operation $operation,
        Specification $specification
    ): ClassMethod {
        $headers = $this->getSecurityHeaders($operation, $specification);
        if (!empty($request->getBodyContentTypes())) {
            $headers['Content-Type'] = $this->builder->localPropertyFetch('contentType');
        }
        $returnVal  = $this->builder->array($headers);
        $fieldsArr  = [];
        $returnType = 'array';
        foreach ($fields as $field) {
            $fieldsArr[$field->getName()] = $this->builder->localPropertyFetch($field->getPhpVariableName());
        }

        if (!empty($fieldsArr)) {
            $returnVal = $this->builder->funcCall(
                'array_merge',
                [$returnVal, $this->generateParametersFromFields($fieldsArr)]
            );
        }

        return $this->builder
            ->method('getHeaders')
            ->makePublic()
            ->addStmt($this->builder->return($returnVal))
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
    }

    private function getSecurityHeaders(Operation $operation, Specification $specification): array
    {
        $headers = [];

        foreach ($this->securityStrategies as $securityStrategy) {
            /** @var SecurityStrategyInterface $securityStrategy */
            $headers += $securityStrategy->getSecurityHeaders($operation, $specification);
        }

        return $headers;
    }

    private function generateParametersFromFields(array $fields): FuncCall
    {
        $filterCallbackBody = $this->builder->return(
            $this->builder->notEquals($this->builder->val(null), $this->builder->var('value'))
        );

        $filterCallback = $this->builder->closure(
            [$filterCallbackBody],
            [$this->builder->param('value')->getNode()]
        );

        $filter = $this->builder->funcCall(
            'array_filter',
            [$this->builder->array($fields), $filterCallback]
        );

        $this->addImport(CopiedNamespace::getImport($this->baseNamespace, SerializableInterface::class));
        $closureVariable = $this->builder->var('value');
        $closureBody     = $this->builder->return(
            $this->builder->ternary(
                $this->builder->instanceOf(
                    $closureVariable,
                    $this->builder->className('SerializableInterface')
                ),
                $this->builder->methodCall(
                    $closureVariable,
                    'toArray'
                ),
                $closureVariable
            )
        );

        return $this->builder->funcCall(
            'array_map',
            [
                $this->builder->closure(
                    [$closureBody],
                    [$this->builder->param('value')->getNode()]
                ),
                $filter,
            ]
        );
    }

    private function sortParameters(ParameterNode ...$parameterNodes): array
    {
        usort(
            $parameterNodes,
            static function (ParameterNode $paramA, ParameterNode $paramB) {
                return $paramA->default <=> $paramB->default;
            }
        );

        return $parameterNodes;
    }
}
