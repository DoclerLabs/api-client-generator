<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\ParameterBuilder;
use DoclerLabs\ApiClientGenerator\Ast\ParameterNode;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
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

    public const SUBDIRECTORY = 'Request/Mapper/';

    public function __construct(
        string $baseNamespace,
        CodeBuilder $builder,
        PhpVersion $phpVersion,
        private HttpMessageImplementationStrategy $messageImplementation
    ) {
        parent::__construct($baseNamespace, $builder, $phpVersion);
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
            'BodySerializer',
            readonly: true
        );
        $properties[] = $this->builder->localProperty(
            $querySerializerPropertyName,
            'QuerySerializer',
            'QuerySerializer',
            readonly: true
        );

        /** @var ParameterBuilder[] $parameters */
        $parameters   = [];
        $parameters[] = $this->builder->param($bodySerializerPropertyName)->setType('BodySerializer');
        $parameters[] = $this->builder->param($querySerializerPropertyName)->setType('QuerySerializer');

        if ($this->phpVersion->isConstructorPropertyPromotionSupported()) {
            foreach ($parameters as $parameter) {
                $parameter->makePrivate();
            }
        }
        if ($this->phpVersion->isReadonlyPropertiesSupported()) {
            foreach ($parameters as $parameter) {
                $parameter->makeReadonly();
            }
        }

        $parameters = array_map(
            static fn (ParameterBuilder $parameter): ParameterNode => $parameter->getNode(),
            $parameters
        );

        $paramInits[] = $this->builder->assign(
            $this->builder->localPropertyFetch($bodySerializerPropertyName),
            $this->builder->var($bodySerializerPropertyName)
        );
        $paramInits[] = $this->builder->assign(
            $this->builder->localPropertyFetch($querySerializerPropertyName),
            $this->builder->var($querySerializerPropertyName)
        );

        $constructor = $this
            ->builder
            ->method('__construct')
            ->makePublic()
            ->addParams($parameters)
            ->composeDocBlock($parameters);

        if (!$this->phpVersion->isConstructorPropertyPromotionSupported()) {
            $constructor->addStmts($paramInits);
        }

        $constructor = $constructor->getNode();

        $className    = $this->messageImplementation->getRequestMapperClassName();
        $classBuilder = $this
            ->builder
            ->class($className)
            ->implement('RequestMapperInterface')
            ->addStmt($constructor)
            ->addStmt($this->generateMap());

        if (!$this->phpVersion->isConstructorPropertyPromotionSupported()) {
            $classBuilder->addStmts($properties);
        }

        $this->registerFile($fileRegistry, $classBuilder, self::SUBDIRECTORY, self::NAMESPACE_SUBPATH);
    }

    protected function generateMap(): ClassMethod
    {
        $this
            ->addImport(PsrRequestInterface::class, 'PsrRequestInterface')
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, RequestInterface::class));

        $requestParam = $this
            ->builder
            ->param('request')
            ->setType('RequestInterface')
            ->getNode();

        return $this
            ->messageImplementation
            ->generateRequestMapMethod()
            ->makePublic()
            ->addParam($requestParam)
            ->setReturnType('PsrRequestInterface')
            ->composeDocBlock([$requestParam], 'PsrRequestInterface')
            ->getNode();
    }
}
