<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Ast\Builder;

use PhpParser\Builder\Enum_;

class EnumBuilder extends Enum_
{
    public function getName(): string
    {
        return $this->name;
    }
}
