<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientBase\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Entity\Request;
use DoclerLabs\ApiClientGenerator\Entity\RequestFieldRegistry;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\RequestNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use JsonSerializable;
use PhpParser\Node\Stmt\ClassMethod;

class RequestGenerator extends MutatorAccessorClassGeneratorAbstract
{
    public const NAMESPACE_SUBPATH = '\\Request';
    public const SUBDIRECTORY      = 'Request/';
    private string $baseNamespace;

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $this->baseNamespace = $fileRegistry->getBaseNamespace();
        foreach ($specification->getOperations() as $operation) {
            $this->generateRequest($fileRegistry, $operation);
        }
    }

    protected function generateRequest(PhpFileCollection $fileRegistry, Operation $operation): void
    {
        $className = RequestNaming::getClassName($operation);
        $request   = $operation->getRequest();

        $this->addImport(RequestInterface::class);

        $classBuilder = $this->builder
            ->class($className)
            ->implement('RequestInterface')
            ->addStmts($this->generateEnums($request))
            ->addStmts($this->generateProperties($request))
            ->addStmt($this->generateConstructor($request))
            ->addStmts($this->generateSetters($request))
            ->addStmt($this->generateGetMethod($request))
            ->addStmt($this->generateGetRoute($request))
            ->addStmts($this->generateGetParametersMethods($request->getFields()));

        $this->registerFile($fileRegistry, $classBuilder, self::SUBDIRECTORY, self::NAMESPACE_SUBPATH);
    }

    protected function generateEnums(Request $request): array
    {
        $statements = [];
        foreach ($request->getFields() as $origin => $fields) {
            foreach ($fields as $field) {
                foreach ($this->generateEnumStatements($field) as $statement) {
                    $statements[] = $statement;
                }
            }
        }

        return $statements;
    }

    protected function generateProperties(Request $request): array
    {
        $statements = [];
        foreach ($request->getFields() as $origin => $fields) {
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

        return $statements;
    }

    protected function generateConstructor(Request $request): ?ClassMethod
    {
        $params     = [];
        $paramInits = [];
        foreach ($request->getFields() as $origin => $fields) {
            foreach ($fields as $field) {
                if ($field->isRequired()) {
                    $enumStmt = $this->generateEnumValidation($field, $this->baseNamespace);
                    if ($enumStmt !== null) {
                        $paramInits[] = $enumStmt;
                    }
                    $params[] = $this->builder
                        ->param($field->getPhpVariableName())
                        ->setType($field->getPhpTypeHint())
                        ->getNode();

                    $paramInits[] = $this->builder->assign(
                        $this->builder->localPropertyFetch($field->getPhpVariableName()),
                        $this->builder->var($field->getPhpVariableName())
                    );
                }
            }
        }
        if (empty($params)) {
            return null;
        }

        return $this->builder
            ->method('__construct')
            ->makePublic()
            ->addParams($params)
            ->addStmts($paramInits)
            ->composeDocBlock($params)
            ->getNode();
    }

    protected function generateSetters(Request $request): array
    {
        $statements = [];
        foreach ($request->getFields() as $origin => $fields) {
            foreach ($fields as $field) {
                if (!$field->isRequired()) {
                    $statements[] = $this->generateSet($field, $this->baseNamespace);
                }
            }
        }

        return $statements;
    }

    protected function generateGetMethod(Request $request): ClassMethod
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

    protected function generateGetRoute(Request $request): ClassMethod
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

    protected function generateGetParametersMethods(RequestFieldRegistry $fields): array
    {
        $methods   = [];
        $methods[] = $this->generateGetParametersMethod(
            'getQueryParameters',
            $fields->getQueryFields()
        );
        $methods[] = $this->generateGetParametersMethod(
            'getCookies',
            $fields->getCookieFields()
        );
        $methods[] = $this->generateGetParametersMethod(
            'getHeaders',
            $fields->getHeaderFields()
        );
        $methods[] = $this->generateGetBody($fields->getBody());

        return $methods;
    }

    protected function generateGetParametersMethod(string $methodName, array $fields): ClassMethod
    {
        $returnVal  = $this->builder->array([]);
        $fieldsArr  = [];
        $returnType = 'array';
        foreach ($fields as $field) {
            $fieldName             = $field->getName();
            $fieldsArr[$fieldName] = $this->builder->localPropertyFetch($field->getPhpVariableName());
        }

        if (!empty($fieldsArr)) {
            $filterCallbackBody = $this->builder->return(
                $this->builder->notEquals($this->builder->val(null), $this->builder->var('value'))
            );
            $filterCallback     = $this->builder->closure(
                [$filterCallbackBody],
                [$this->builder->param('value')->getNode()]
            );
            $filter             = $this->builder->funcCall(
                'array_filter',
                [$this->builder->array($fieldsArr), $filterCallback]
            );

            $this->addImport(JsonSerializable::class);
            $closureVariable = $this->builder->var('value');
            $closureBody     = $this->builder->return(
                $this->builder->ternary(
                    $this->builder->instanceOf(
                        $closureVariable,
                        $this->builder->className(JsonSerializable::class)
                    ),
                    $this->builder->funcCall(
                        'json_decode',
                        [
                            $this->builder->funcCall(
                                'json_encode',
                                [$closureVariable]
                            ),
                            false,
                        ]
                    ),
                    $closureVariable
                )
            );
            $returnVal       = $this->builder->funcCall(
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

        return $this->builder
            ->method($methodName)
            ->makePublic()
            ->addStmt($this->builder->return($returnVal))
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
    }

    protected function generateGetBody(?Field $body): ClassMethod
    {
        if ($body !== null) {
            $returnType = $body->getPhpTypeHint();

            return $this->builder
                ->method('getBody')
                ->makePublic()
                ->addStmt($this->builder->return($this->builder->localPropertyFetch($body->getPhpVariableName())))
                ->setReturnType($returnType)
                ->composeDocBlock([], $returnType)
                ->getNode();
        }

        return $this->builder
            ->method('getBody')
            ->makePublic()
            ->addStmt($this->builder->return($this->builder->val(null)))
            ->getNode();
    }
}
