<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast\Builder;

use PhpParser\Builder;
use PhpParser\Builder\Class_;
use PhpParser\Node\Stmt;

class ClassBuilder extends Class_
{
    /**
     * @param Builder|Stmt|null $stmt
     *
     * @return $this|ClassBuilder
     */
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
