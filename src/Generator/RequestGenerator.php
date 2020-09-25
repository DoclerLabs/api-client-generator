<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Entity\Request;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\CopiedNamespace;
use DoclerLabs\ApiClientGenerator\Naming\RequestNaming;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\SerializableRequestBodyInterface;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\ClassMethod;

class RequestGenerator extends MutatorAccessorClassGeneratorAbstract
{
    public const NAMESPACE_SUBPATH = '\\Request';
    public const SUBDIRECTORY      = 'Request/';

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        foreach ($specification->getOperations() as $operation) {
            $this->generateRequest($fileRegistry, $operation);
        }
    }

    protected function generateRequest(PhpFileCollection $fileRegistry, Operation $operation): void
    {
        $className = RequestNaming::getClassName($operation);
        $request   = $operation->getRequest();

        $classBuilder = $this->builder
            ->class($className)
            ->implement('RequestInterface')
            ->addStmts($this->generateEnums($request))
            ->addStmts($this->generateProperties($request))
            ->addStmt($this->generateConstructor($request))
            ->addStmts($this->generateSetters($request))
            ->addStmt($this->generateGetContentType($request))
            ->addStmt($this->generateGetMethod($request))
            ->addStmt($this->generateGetRoute($request))
            ->addStmts($this->generateGetParametersMethods($request));

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
                        ->setType($field->getPhpTypeHint(), $field->isNullable())
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

    protected function generateGetContentType(Request $request): ClassMethod
    {
        $return     = $this->builder->return($this->builder->val($request->getContentType()));
        $returnType = 'string';

        return $this->builder
            ->method('getContentType')
            ->makePublic()
            ->addStmt($return)
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
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

    protected function generateGetParametersMethods(Request $request): array
    {
        $methods   = [];
        $fields    = $request->getFields();
        $methods[] = $this->generateGetParametersMethod(
            'getQueryParameters',
            $fields->getQueryFields()
        );
        $methods[] = $this->generateGetParametersMethod(
            'getCookies',
            $fields->getCookieFields()
        );
        $methods[] = $this->generateGetHeadersMethod($request, $fields->getHeaderFields());
        $methods[] = $this->generateGetBody($fields->getBody());

        return $methods;
    }

    protected function generateGetParametersMethod(string $methodName, array $fields): ClassMethod
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

    protected function generateGetBody(?Field $body): ClassMethod
    {
        if ($body !== null) {
            $returnType = $body->getPhpTypeHint();

            return $this->builder
                ->method('getBody')
                ->makePublic()
                ->setReturnType($returnType, $body->isNullable())
                ->addStmt($this->builder->return($this->builder->localPropertyFetch($body->getPhpVariableName())))
                ->composeDocBlock([], $returnType)
                ->getNode();
        }

        return $this->builder
            ->method('getBody')
            ->makePublic()
            ->setReturnType(null)
            ->getNode();
    }

    private function generateGetHeadersMethod(Request $request, array $fields)
    {
        $headers = [];
        if ($request->getContentType() !== '') {
            $headers['Content-Type'] = $this->builder->val($request->getContentType());
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

        $this->addImport(CopiedNamespace::getImport($this->baseNamespace, SerializableRequestBodyInterface::class));
        $closureVariable = $this->builder->var('value');
        $closureBody     = $this->builder->return(
            $this->builder->ternary(
                $this->builder->instanceOf(
                    $closureVariable,
                    $this->builder->className('SerializableRequestBody')
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
}
