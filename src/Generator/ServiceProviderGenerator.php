<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\ResponseMapperNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Psr\Container\ContainerInterface;

class ServiceProviderGenerator extends GeneratorAbstract
{
    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $this->addImport(ContainerInterface::class);

        $classBuilder = $this->builder
            ->class('ServiceProvider')
            ->addStmt($this->generateRegisterResponseMappers($specification));

        $this->registerFile($fileRegistry, $classBuilder);
    }

    protected function generateRegisterResponseMappers(Specification $specification): ClassMethod
    {
        $statements = [];

        $param = $this->builder
            ->param('container')
            ->setType('ContainerInterface')
            ->getNode();

        $containerVariable = $this->builder->var('container');
        $compositeFields   = $specification->getCompositeResponseFields()->getUniqueByPhpClassName();
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

            $closureStatements[] = $this->builder->return($this->buildMapperDependencies($field, $containerVariable));

            $closure = $this->builder->closure($closureStatements, [], [$containerVariable], $mapperClass);

            $statements[] = $this->builder->methodCall($containerVariable, 'add', [$mapperClassConst, $closure]);
        }

        return $this->builder
            ->method('registerResponseMappers')
            ->addParam($param)
            ->addStmts($statements)
            ->setReturnType(null)
            ->composeDocBlock([$param], '', [])
            ->getNode();
    }

    private function buildMapperDependencies(Field $field, Variable $containerVariable): New_
    {
        $dependencies = [];
        if ($field->isObject()) {
            $alreadyInjected = [];
            foreach ($field->getObjectProperties() as $subfield) {
                if ($subfield->isComposite() && !isset($alreadyInjected[$subfield->getPhpClassName()])) {
                    $getMethodArg   = $this->builder->classConstFetch(
                        ResponseMapperNaming::getClassName($subfield),
                        'class'
                    );
                    $dependencies[] = $this->builder->methodCall($containerVariable, 'get', [$getMethodArg]);

                    $alreadyInjected[$subfield->getPhpClassName()] = true;
                }
            }
        }
        if ($field->isArrayOfObjects()) {
            $getMethodArg   = $this->builder->classConstFetch(
                ResponseMapperNaming::getClassName($field->getArrayItem()),
                'class'
            );
            $dependencies[] = $this->builder->methodCall($containerVariable, 'get', [$getMethodArg]);
        }

        return $this->builder->new(ResponseMapperNaming::getClassName($field), $dependencies);
    }
}
