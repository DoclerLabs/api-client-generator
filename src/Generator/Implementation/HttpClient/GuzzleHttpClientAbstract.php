<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClient;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;

abstract class GuzzleHttpClientAbstract
{
    protected CodeBuilder $builder;

    public function __construct(CodeBuilder $builder)
    {
        $this->builder = $builder;
    }

    protected function getConfigStatements(Variable $configVariable): array
    {
        $statements = [];
        $default    = $this->builder->var('default');
        $baseUri    = $this->builder->var('baseUri');
        $options    = $this->builder->var('options');

        $statements[] = $this->generateBaseUriValidation($baseUri);

        $defaultItems = [
            'base_uri'    => $baseUri,
            'timeout'     => $this->builder->val(3),
            'http_errors' => $this->builder->val(false),
        ];

        $statements[] = $this->builder->assign($default, $this->builder->array($defaultItems));

        $statements[] = $this->builder->assign(
            $configVariable,
            $this->builder->funcCall('array_replace_recursive', [$default, $options])
        );

        return $statements;
    }

    protected function generateBaseUriValidation(Variable $baseUri): Stmt
    {
        $lastCharacterFunction = $this->builder->funcCall('substr', [$baseUri, $this->builder->val(-1)]);
        $conditionStatement    = $this->builder->notEquals($lastCharacterFunction, $this->builder->val('/'));

        $exceptionMessage = 'Base URI should end with the `/` symbol.';
        $throwStatement   = $this->builder->throw(
            'InvalidArgumentException',
            $this->builder->val($exceptionMessage)
        );

        return $this->builder->if($conditionStatement, [$throwStatement]);
    }
}