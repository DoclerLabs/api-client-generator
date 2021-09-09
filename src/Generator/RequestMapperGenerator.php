<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\CopiedNamespace;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\CookieJar;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\BodySerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\QuerySerializer;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Stmt\ClassMethod;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;

class RequestMapperGenerator extends MutatorAccessorClassGeneratorAbstract
{
    public const NAMESPACE_SUBPATH = '\\Request\\Mapper';
    public const SUBDIRECTORY      = 'Request/Mapper/';
    private HttpMessageImplementationStrategy $messageImplementation;

    public function __construct(
        string $baseNamespace,
        CodeBuilder $builder,
        HttpMessageImplementationStrategy $messageImplementation
    ) {
        parent::__construct($baseNamespace, $builder);
        $this->messageImplementation = $messageImplementation;
    }

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $this
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, BodySerializer::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, QuerySerializer::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, CookieJar::class));

        foreach ($this->messageImplementation->getInitMessageImports() as $import) {
            $this->addImport($import);
        }

        $bodySerializerPropertyName  = 'bodySerializer';
        $querySerializerPropertyName = 'querySerializer';

        $properties   = [];
        $properties[] = $this->builder->localProperty(
            $bodySerializerPropertyName,
            'BodySerializer',
            'BodySerializer'
        );
        $properties[] = $this->builder->localProperty(
            $querySerializerPropertyName,
            'QuerySerializer',
            'QuerySerializer'
        );

        $parameters   = [];
        $parameters[] = $this->builder
            ->param($bodySerializerPropertyName)
            ->setType('BodySerializer')
            ->getNode();
        $parameters[] = $this->builder
            ->param($querySerializerPropertyName)
            ->setType('QuerySerializer')
            ->getNode();

        $paramInits[] = $this->builder->assign(
            $this->builder->localPropertyFetch($bodySerializerPropertyName),
            $this->builder->var($bodySerializerPropertyName)
        );
        $paramInits[] = $this->builder->assign(
            $this->builder->localPropertyFetch($querySerializerPropertyName),
            $this->builder->var($querySerializerPropertyName)
        );

        $constructor = $this->builder
            ->method('__construct')
            ->makePublic()
            ->addParams($parameters)
            ->addStmts($paramInits)
            ->composeDocBlock($parameters)
            ->getNode();

        $className    = $this->messageImplementation->getRequestMapperClassName();
        $classBuilder = $this->builder
            ->class($className)
            ->implement('RequestMapperInterface')
            ->addStmts($properties)
            ->addStmt($constructor)
            ->addStmt($this->generateMap());

        $this->registerFile($fileRegistry, $classBuilder, self::SUBDIRECTORY, self::NAMESPACE_SUBPATH);
    }

    protected function generateMap(): ClassMethod
    {
        $this
            ->addImport(PsrRequestInterface::class, 'PsrRequestInterface')
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, RequestInterface::class));

        $requestParam = $this->builder
            ->param('request')
            ->setType('RequestInterface')
            ->getNode();

        return $this->messageImplementation
            ->generateRequestMapMethod()
            ->makePublic()
            ->addParam($requestParam)
            ->setReturnType('PsrRequestInterface')
            ->composeDocBlock([$requestParam], 'PsrRequestInterface')
            ->getNode();
    }
}
