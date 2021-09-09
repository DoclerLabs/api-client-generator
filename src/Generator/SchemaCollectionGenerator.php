<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use ArrayIterator;
use Countable;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\SchemaCollectionNaming;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use IteratorAggregate;
use JsonSerializable;
use PhpParser\Node\Stmt\ClassMethod;

class SchemaCollectionGenerator extends GeneratorAbstract
{
    public const  SUBDIRECTORY        = 'Schema/';
    public const  NAMESPACE_SUBPATH   = '\\Schema';
    private const INTERNAL_ARRAY_NAME = 'items';

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $compositeFields = $specification->getCompositeFields()->getUniqueByPhpClassName();
        foreach ($compositeFields as $field) {
            if ($field->isArrayOfObjects()) {
                $this->generateSchemaCollection($field, $fileRegistry);
            }
        }
    }

    protected function generateSchemaCollection(Field $field, PhpFileCollection $fileRegistry): void
    {
        $this
            ->addImport(Countable::class)
            ->addImport(IteratorAggregate::class)
            ->addImport(JsonSerializable::class)
            ->addImport(ArrayIterator::class);

        $className    = $field->getPhpClassName();
        $classBuilder = $this->builder
            ->class($className)
            ->implement('IteratorAggregate', 'SerializableInterface', 'Countable', 'JsonSerializable')
            ->addStmt(
                $this->builder->localProperty(
                    self::INTERNAL_ARRAY_NAME,
                    'array',
                    SchemaCollectionNaming::getArrayDocType($field->getArrayItem())
                )
            )
            ->addStmt($this->generateConstructor($field->getArrayItem()))
            ->addStmt($this->generateToArray($field))
            ->addStmt($this->generateJsonSerialize())
            ->addStmt($this->generateGetIterator($field))
            ->addStmt($this->generateCount())
            ->addStmt($this->generateFirst($field->getArrayItem()));

        $this->registerFile($fileRegistry, $classBuilder, self::SUBDIRECTORY, self::NAMESPACE_SUBPATH);
    }

    protected function generateConstructor(Field $item): ClassMethod
    {
        $param = $this->builder
            ->param(self::INTERNAL_ARRAY_NAME)
            ->setType($item->getPhpTypeHint(), $item->isNullable())
            ->makeVariadic()
            ->getNode();

        $paramInit = $this->builder->assign(
            $this->builder->localPropertyFetch(self::INTERNAL_ARRAY_NAME),
            $this->builder->var(self::INTERNAL_ARRAY_NAME)
        );

        $paramDoc = $this->builder
            ->param(self::INTERNAL_ARRAY_NAME)
            ->setType($item->getPhpDocType() . '[]')
            ->getNode();

        return $this->builder
            ->method('__construct')
            ->makePublic()
            ->addParam($param)
            ->addStmt($paramInit)
            ->composeDocBlock([$paramDoc])
            ->getNode();
    }

    protected function generateToArray(Field $field): ClassMethod
    {
        $statements = [];

        $returnVar    = $this->builder->var('return');
        $statements[] = $this->builder->assign($returnVar, $this->builder->array([]));

        $itemVar         = $this->builder->var('item');
        $itemToArrayCall = $this->builder->methodCall($itemVar, 'toArray');

        $statements[] = $this->builder->foreach(
            $this->builder->localPropertyFetch(self::INTERNAL_ARRAY_NAME),
            $itemVar,
            [
                $this->builder->appendToArray($returnVar, $itemToArrayCall),
            ]
        );

        $statements[] = $this->builder->return($returnVar);

        return $this->builder
            ->method('toArray')
            ->makePublic()
            ->addStmts($statements)
            ->setReturnType(FieldType::PHP_TYPE_ARRAY)
            ->composeDocBlock([], FieldType::PHP_TYPE_ARRAY)
            ->getNode();
    }

    protected function generateGetIterator(Field $field): ClassMethod
    {
        $arg    = $this->builder->localPropertyFetch(self::INTERNAL_ARRAY_NAME);
        $return = $this->builder->return($this->builder->new('ArrayIterator', [$arg]));

        return $this->builder
            ->method('getIterator')
            ->makePublic()
            ->addStmt($return)
            ->setReturnType('ArrayIterator')
            ->composeDocBlock([], SchemaCollectionNaming::getArrayDocType($field->getArrayItem()))
            ->getNode();
    }

    protected function generateCount(): ClassMethod
    {
        $return = $this->builder->return(
            $this->builder->funcCall('count', [$this->builder->localPropertyFetch(self::INTERNAL_ARRAY_NAME)])
        );

        return $this->builder
            ->method('count')
            ->makePublic()
            ->addStmt($return)
            ->setReturnType(FieldType::PHP_TYPE_INTEGER)
            ->composeDocBlock([], FieldType::PHP_TYPE_INTEGER)
            ->getNode();
    }

    protected function generateFirst(Field $arrayItem): ClassMethod
    {
        $firstVar    = $this->builder->var('first');
        $resetAssign = $this->builder->assign(
            $firstVar,
            $this->builder->funcCall('reset', [$this->builder->localPropertyFetch(self::INTERNAL_ARRAY_NAME)])
        );

        $ifCondition = $this->builder->equals($firstVar, $this->builder->val(false));
        $if          = $this->builder->if($ifCondition, [$this->builder->return($this->builder->val(null))]);
        $return      = $this->builder->return($firstVar);

        return $this->builder
            ->method('first')
            ->makePublic()
            ->addStmt($resetAssign)
            ->addStmt($if)
            ->addStmt($return)
            ->setReturnType($arrayItem->getPhpTypeHint(), true)
            ->composeDocBlock([], $arrayItem->getReferenceName() . '|null')
            ->getNode();
    }
}
