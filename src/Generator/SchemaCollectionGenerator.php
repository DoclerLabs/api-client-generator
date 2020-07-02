<?php

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
            ->addImport(JsonSerializable::class)
            ->addImport(Countable::class)
            ->addImport(IteratorAggregate::class)
            ->addImport(ArrayIterator::class);

        $className       = $field->getPhpClassName();
        $propertyDocType = SchemaCollectionNaming::getArrayDocType($field->getStructure()->getArrayItem());

        $classBuilder = $this->builder
            ->class($className)
            ->implement('IteratorAggregate', 'JsonSerializable', 'Countable')
            ->addStmt($this->builder->localProperty(self::INTERNAL_ARRAY_NAME, $propertyDocType))
            ->addStmt($this->generateConstructor($field->getStructure()->getArrayItem()))
            ->addStmt($this->generateToArray($field))
            ->addStmt($this->generateGetIterator($field))
            ->addStmt($this->generateJsonSerialize($field))
            ->addStmt($this->generateCount())
            ->addStmt($this->generateFirst($field->getStructure()->getArrayItem()));

        $this->registerFile($fileRegistry, $classBuilder, self::SUBDIRECTORY, self::NAMESPACE_SUBPATH);
    }

    protected function generateConstructor(Field $item): ClassMethod
    {
        $param = $this->builder
            ->param(self::INTERNAL_ARRAY_NAME)
            ->setType($item->getPhpTypeHint())
            ->makeVariadic()
            ->getNode();

        $paramInit = $this->builder->assign(
            $this->builder->localPropertyFetch(self::INTERNAL_ARRAY_NAME),
            $this->builder->var(self::INTERNAL_ARRAY_NAME)
        );

        $paramDoc = $this->builder
            ->param(self::INTERNAL_ARRAY_NAME)
            ->setType(SchemaCollectionNaming::getArrayDocType($item))
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
        $return = $this->builder->return($this->builder->localPropertyFetch(self::INTERNAL_ARRAY_NAME));

        return $this->builder
            ->method('toArray')
            ->makePublic()
            ->addStmt($return)
            ->setReturnType(FieldType::PHP_TYPE_ARRAY)
            ->composeDocBlock([], SchemaCollectionNaming::getArrayDocType($field->getStructure()->getArrayItem()))
            ->getNode();
    }

    protected function generateGetIterator(Field $field): ClassMethod
    {
        $arg    = $this->builder->localMethodCall('toArray');
        $return = $this->builder->return($this->builder->new('ArrayIterator', [$arg]));

        return $this->builder
            ->method('getIterator')
            ->makePublic()
            ->addStmt($return)
            ->setReturnType('ArrayIterator')
            ->composeDocBlock([], SchemaCollectionNaming::getArrayDocType($field->getStructure()->getArrayItem()))
            ->getNode();
    }

    protected function generateJsonSerialize(Field $field): ClassMethod
    {
        $return = $this->builder->return($this->builder->localMethodCall('toArray'));

        return $this->builder
            ->method('jsonSerialize')
            ->makePublic()
            ->addStmt($return)
            ->setReturnType(FieldType::PHP_TYPE_ARRAY)
            ->composeDocBlock([], SchemaCollectionNaming::getArrayDocType($field->getStructure()->getArrayItem()))
            ->getNode();
    }

    protected function generateCount(): ClassMethod
    {
        $return = $this->builder->return(
            $this->builder->funcCall('count', [$this->builder->localMethodCall('toArray')])
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
        $itemsVar    = $this->builder->var('items');
        $firstVar    = $this->builder->var('first');
        $itemsAssign = $this->builder->assign($itemsVar, $this->builder->localMethodCall('toArray'));
        $resetAssign = $this->builder->assign(
            $firstVar,
            $this->builder->funcCall('reset', [$itemsVar])
        );

        $ifCondition = $this->builder->equals($firstVar, $this->builder->val(false));
        $if          = $this->builder->if($ifCondition, [$this->builder->return($this->builder->val(null))]);
        $return      = $this->builder->return($firstVar);

        return $this->builder
            ->method('first')
            ->makePublic()
            ->addStmt($itemsAssign)
            ->addStmt($resetAssign)
            ->addStmt($if)
            ->addStmt($return)
            ->composeDocBlock([], $arrayItem->getReferenceName() . '|null')
            ->getNode();
    }
}
