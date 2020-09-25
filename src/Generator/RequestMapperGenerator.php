<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\CopiedNamespace;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\BodySerializer;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Stmt\ClassMethod;
use Psr\Http\Message\ServerRequestInterface;

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
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, BodySerializer::class));

        foreach ($this->messageImplementation->getInitMessageImports() as $import) {
            $this->addImport($import);
        }

        $serializerPropertyName = 'serializer';

        $properties   = [];
        $properties[] = $this->builder->localProperty(
            $serializerPropertyName,
            'BodySerializer',
            'BodySerializer'
        );

        $parameters   = [];
        $parameters[] = $this->builder
            ->param($serializerPropertyName)
            ->setType('BodySerializer')
            ->getNode();

        $paramInits[] = $this->builder->assign(
            $this->builder->localPropertyFetch($serializerPropertyName),
            $this->builder->var($serializerPropertyName)
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
            ->addImport(ServerRequestInterface::class)
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, RequestInterface::class));

        $requestParam = $this->builder
            ->param('request')
            ->setType('RequestInterface')
            ->getNode();

        return $this->messageImplementation
            ->generateRequestMapMethod()
            ->makePublic()
            ->addParam($requestParam)
            ->setReturnType('ServerRequestInterface')
            ->composeDocBlock([$requestParam], 'ServerRequestInterface')
            ->getNode();
    }
}
