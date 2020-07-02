<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Builder;

use PhpParser\Builder\Class_;

class ClassBuilder extends Class_
{
    public function addStmt($stmt)
    {
        if ($stmt === null) {
            return $this;
        }

        return parent::addStmt($stmt);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
