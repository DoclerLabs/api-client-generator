<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DateTimeInterface;
use DoclerLabs\ApiClientBase\Exception\RequestValidationException;
use DoclerLabs\ApiClientBase\Json\Json;
use DoclerLabs\ApiClientGenerator\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\SchemaNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

abstract class MutatorAccessorClassGeneratorAbstract extends GeneratorAbstract
{
    protected CodeBuilder $builder;

    abstract public function generate(Specification $specification, PhpFileCollection $fileRegistry): void;

    protected function generateSet(Field $field, string $baseNamespace): ClassMethod
    {
        $statements         = [];
        $thrownExceptionMap = [];
        $enumStmt           = $this->generateEnumValidation($field, $baseNamespace);
        if ($enumStmt !== null) {
            $statements[]                                     = $enumStmt;
            $thrownExceptionMap['RequestValidationException'] = true;
        }

        $docType = $field->getPhpDocType($field->isNullable());
        $param   = $this->builder
            ->param($field->getPhpVariableName())
            ->setType($field->getPhpTypeHint())
            ->setDocBlockType($docType)
            ->getNode();

        $statements[] = $this->builder->assign(
            $this->builder->localPropertyFetch($field->getPhpVariableName()),
            $this->builder->var($field->getPhpVariableName())
        );

        $return     = $this->builder->return($this->builder->var('this'));
        $returnType = 'self';

        return $this->builder
            ->method($this->getSetMethodName($field))
            ->makePublic()
            ->addParam($param)
            ->addStmts($statements)
            ->addStmt($return)
            ->setReturnType($returnType)
            ->composeDocBlock([$param], $returnType, array_keys($thrownExceptionMap))
            ->getNode();
    }

    protected function generateGet(Field $field): ClassMethod
    {
        $returnType = $field->isRequired() ? $field->getPhpTypeHint() : '';
        $phpDocType = $field->getPhpDocType();

        $return = $this->builder->return($this->builder->localPropertyFetch($field->getPhpVariableName()));

        return $this->builder
            ->method($this->getGetMethodName($field))
            ->makePublic()
            ->addStmt($return)
            ->setReturnType($returnType)
            ->composeDocBlock([], $phpDocType)
            ->getNode();
    }

    protected function generateProperty(Field $field): Property
    {
        if ($field->isDate()) {
            $this->addImport(DateTimeInterface::class);
        }

        $docType = $field->getPhpDocType();

        return $this->builder->localProperty($field->getPhpVariableName(), $docType);
    }

    protected function getSetMethodName(Field $field): string
    {
        return sprintf('set%s', ucfirst($field->getPhpVariableName()));
    }

    protected function getGetMethodName(Field $field): string
    {
        return sprintf('get%s', ucfirst($field->getPhpVariableName()));
    }

    protected function generateEnumStatements(Field $field): array
    {
        $statements = [];
        $enumValues = $field->getEnumValues();
        if (!empty($enumValues)) {
            $enumConstCalls = [];
            foreach ($enumValues as $enumValue) {
                $constName = SchemaNaming::getEnumConstName($field, $enumValue);
                $enumConst = $this->builder->const(
                    $constName,
                    $this->builder->val($enumValue)
                );

                $statements[]     = $this->builder->constants([$enumConst]);
                $enumConstCalls[] = $this->builder->classConstFetch('self', $constName);
            }
            $enumConst = $this->builder->const(
                SchemaNaming::getAllowedEnumConstName($field),
                $this->builder->array($enumConstCalls)
            );

            $statements[] = $this->builder->constants([$enumConst]);
        }

        return $statements;
    }

    protected function generateEnumValidation(Field $root, string $baseNamespace): ?Stmt
    {
        $enumValues = $root->getEnumValues();
        if (empty($enumValues)) {
            return null;
        }

        $this
            ->addImport(Json::class)
            ->addImport(RequestValidationException::class);

        $propertyVar       = $this->builder->var($root->getPhpVariableName());
        $allowedConstFetch = $this->builder->classConstFetch(
            'self',
            SchemaNaming::getAllowedEnumConstName($root)
        );

        $inArrayArgs = [
            $propertyVar,
            $allowedConstFetch,
            $this->builder->val(true),
        ];
        $ifCondition = $this->builder->not($this->builder->funcCall('in_array', $inArrayArgs));

        $exceptionMessage = $this->builder->funcCall(
            'sprintf',
            [
                'Invalid %s value. Given: `%s` Allowed: %s',
                $root->getName(),
                $propertyVar,
                $this->builder->staticCall('Json', 'encode', [$allowedConstFetch]),
            ]
        );

        $ifStmt = $this->builder->throw('RequestValidationException', $exceptionMessage);

        return $this->builder->if($ifCondition, [$ifStmt]);
    }
}
