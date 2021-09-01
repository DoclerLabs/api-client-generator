<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Constraint\ConstraintCollection;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use JsonSerializable;
use PhpParser\Node\Stmt\ClassMethod;
use stdClass;

class FreeFormSchemaGenerator extends MutatorAccessorClassGeneratorAbstract
{
    private const FREE_FORM_SCHEMA_VARIABLE = 'data';

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $compositeFields = $specification->getCompositeFields()->getUniqueByPhpClassName();
        foreach ($compositeFields as $field) {
            if ($field->isFreeFormObject()) {
                $this->generateFreeFormSchema($field, $fileRegistry);
            }
        }
    }

    private function generateFreeFormSchema(Field $field, PhpFileCollection $fileRegistry): void
    {
        $this->addImport(JsonSerializable::class);
        $this->addImport(stdClass::class);

        $className     = $field->getPhpClassName();
        $freeFormField = $this->generateFreeFormField();

        $classBuilder = $this->builder
            ->class($className)
            ->implement('SerializableInterface', 'JsonSerializable')
            ->addStmt($this->generateProperty($freeFormField))
            ->addStmt($this->generateConstructor())
            ->addStmt($this->generateGet($freeFormField))
            ->addStmt($this->generateToArray())
            ->addStmt($this->generateJsonSerialize());

        $this->registerFile($fileRegistry, $classBuilder, SchemaGenerator::SUBDIRECTORY, SchemaGenerator::NAMESPACE_SUBPATH);
    }

    private function generateConstructor(): ClassMethod
    {
        $param = $this->builder
            ->param(self::FREE_FORM_SCHEMA_VARIABLE)
            ->setType(FieldType::PHP_TYPE_ARRAY)
            ->getNode();

        $paramInit = $this->builder->assign(
            $this->builder->localPropertyFetch(self::FREE_FORM_SCHEMA_VARIABLE),
            $this->builder->castToObject(
                $this->builder->var(self::FREE_FORM_SCHEMA_VARIABLE)
            )
        );

        return $this->builder
            ->method('__construct')
            ->makePublic()
            ->addParam($param)
            ->addStmt($paramInit)
            ->getNode();
    }

    private function generateFreeFormField(): Field
    {
        return new Field(
            self::FREE_FORM_SCHEMA_VARIABLE,
            new FieldType(FieldType::PHP_TYPE_OBJECT),
            new ConstraintCollection(),
            stdClass::class,
            true,
            false,
            true
        );
    }

    private function generateToArray(): ClassMethod
    {
        $return     = $this->builder->return(
            $this->builder->castToArray(
                $this->builder->localPropertyFetch(self::FREE_FORM_SCHEMA_VARIABLE)
            )
        );
        $returnType = FieldType::PHP_TYPE_ARRAY;

        return $this->builder
            ->method('toArray')
            ->makePublic()
            ->addStmt($return)
            ->setReturnType($returnType)
            ->composeDocBlock([], $returnType)
            ->getNode();
    }
}
