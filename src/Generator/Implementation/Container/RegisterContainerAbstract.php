<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\Container;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Generator\ResponseMapperGenerator;
use DoclerLabs\ApiClientGenerator\Naming\ResponseMapperNaming;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;

abstract class RegisterContainerAbstract
{
    protected string      $baseNamespace;
    protected CodeBuilder $builder;
    protected array       $registerImports;

    public function __construct(string $baseNamespace, CodeBuilder $builder)
    {
        $this->baseNamespace = $baseNamespace;
        $this->builder       = $builder;
    }

    public function generateRegisterMethod(array $compositeFields): MethodBuilder
    {
        $statements = [];

        $param = $this->builder
            ->param('container')
            ->setType('Container')
            ->getNode();

        $containerVariable = $this->builder->var('container');

        $registerBodySerializerClosureSubCall = $this->builder->methodCall(
            $this->builder->new('BodySerializer'),
            'add',
            [
                $this->builder->val('application/json'),
                $this->builder->new('JsonContentTypeSerializer'),
            ]
        );
        $registerBodySerializerClosure        = $this->builder->methodCall(
            $registerBodySerializerClosureSubCall,
            'add',
            [
                $this->builder->val('application/x-www-form-urlencoded'),
                $this->builder->new('FormUrlencodedContentTypeSerializer'),
            ]
        );

        $registerBodySerializerClosureStatements[] = $this->builder->return($registerBodySerializerClosure);

        $registerBodySerializerClosure = $this->builder->closure(
            $registerBodySerializerClosureStatements,
            [],
            [],
            'BodySerializer'
        );

        $statements[] = $this->registerClosure(
            $containerVariable,
            $this->builder->classConstFetch('BodySerializer', 'class'),
            $registerBodySerializerClosure
        );
        foreach ($compositeFields as $field) {
            /** @var Field $field */
            $closureStatements       = [];
            $mapperClass             = ResponseMapperNaming::getClassName($field);
            $this->registerImports[] = sprintf(
                '%s%s\\%s',
                $this->baseNamespace,
                ResponseMapperGenerator::NAMESPACE_SUBPATH,
                $mapperClass
            );

            $mapperClassConst = $this->builder->classConstFetch($mapperClass, 'class');

            $closureStatements[] = $this->builder->return($this->buildMapperDependencies($field, $containerVariable));

            $closure = $this->builder->closure($closureStatements, [], [$containerVariable], $mapperClass);

            $statements[] = $this->registerClosure(
                $containerVariable,
                $mapperClassConst,
                $closure
            );
        }

        return $this->builder
            ->method('register')
            ->addParam($param)
            ->addStmts($statements)
            ->composeDocBlock([$param], '', []);
    }

    abstract protected function registerClosure(Variable $containerVariable, Expr $key, Closure $closure): Expr;

    abstract protected function getClosure(Variable $containerVariable, Expr $key): Expr;

    private function buildMapperDependencies(Field $field, Variable $containerVariable): New_
    {
        $dependencies   = [];
        $dependencies[] =
            $this->getClosure($containerVariable, $this->builder->classConstFetch('BodySerializer', 'class'));
        if ($field->isObject()) {
            $alreadyInjected = [];
            foreach ($field->getObjectProperties() as $subfield) {
                if ($subfield->isComposite() && !isset($alreadyInjected[$subfield->getPhpClassName()])) {
                    $getMethodArg   = $this->builder->classConstFetch(
                        ResponseMapperNaming::getClassName($subfield),
                        'class'
                    );
                    $dependencies[] = $this->getClosure($containerVariable, $getMethodArg);

                    $alreadyInjected[$subfield->getPhpClassName()] = true;
                }
            }
        }
        if ($field->isArrayOfObjects()) {
            $getMethodArg   = $this->builder->classConstFetch(
                ResponseMapperNaming::getClassName($field->getArrayItem()),
                'class'
            );
            $dependencies[] = $this->getClosure($containerVariable, $getMethodArg);
        }

        return $this->builder->new(ResponseMapperNaming::getClassName($field), $dependencies);
    }
}