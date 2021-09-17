<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientException\Factory\ResponseExceptionFactory;
use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\Builder\MethodBuilder;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Naming\CopiedNamespace;
use DoclerLabs\ApiClientGenerator\Naming\SchemaMapperNaming;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\Mapper\RequestMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Response\ResponseHandler;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\BodySerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\VdnApiJsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\XmlContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\QuerySerializer;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;

class ServiceProviderGenerator extends GeneratorAbstract
{
    private ContainerImplementationStrategy   $containerImplementation;
    private HttpMessageImplementationStrategy $messageImplementation;

    public function __construct(
        string $baseNamespace,
        CodeBuilder $builder,
        ContainerImplementationStrategy $containerImplementation,
        HttpMessageImplementationStrategy $messageImplementation
    ) {
        parent::__construct($baseNamespace, $builder);
        $this->containerImplementation = $containerImplementation;
        $this->messageImplementation   = $messageImplementation;
    }

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        $this
            ->addImport(ResponseExceptionFactory::class)
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, RequestMapperInterface::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, ResponseHandler::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, BodySerializer::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, QuerySerializer::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, JsonContentTypeSerializer::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, VdnApiJsonContentTypeSerializer::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, FormUrlencodedContentTypeSerializer::class))
            ->addImport(CopiedNamespace::getImport($this->baseNamespace, XmlContentTypeSerializer::class))
            ->addImport(
                sprintf(
                    '%s%s\\%s',
                    $this->baseNamespace,
                    RequestMapperGenerator::NAMESPACE_SUBPATH,
                    $this->messageImplementation->getRequestMapperClassName()
                )
            );

        $compositeFields = $specification->getCompositeResponseFields()->getUniqueByPhpClassName();

        $classBuilder = $this->builder
            ->class('ServiceProvider')
            ->addStmt($this->generateRegisterMethod($specification, $compositeFields));

        foreach ($this->containerImplementation->getContainerRegisterImports() as $import) {
            $this->addImport($import);
        }

        $this->registerFile($fileRegistry, $classBuilder);
    }

    private function generateRegisterMethod(
        Specification $specification,
        array $compositeFields
    ): ClassMethod {
        $statements = [];

        $param = $this->builder
            ->param('container')
            ->setType('Container')
            ->getNode();

        $containerVariable = $this->builder->var('container');

        $requestMapperClosure = $this->builder->closure(
            [
                $this->builder->return(
                    $this->builder->new(
                        $this->messageImplementation->getRequestMapperClassName(),
                        [
                            $this->containerImplementation->getClosure(
                                $containerVariable,
                                $this->builder->classConstFetch(
                                    'BodySerializer',
                                    'class'
                                )
                            ),
                            $this->containerImplementation->getClosure(
                                $containerVariable,
                                $this->builder->classConstFetch(
                                    'QuerySerializer',
                                    'class'
                                )
                            ),
                        ]
                    )
                ),
            ],
            [],
            [$containerVariable],
            'RequestMapperInterface'
        );

        $statements[] = $this->containerImplementation->registerClosure(
            $containerVariable,
            $this->builder->classConstFetch('BodySerializer', 'class'),
            $this->generateBodySerializerClosure($specification)
        );
        $statements[] = $this->containerImplementation->registerClosure(
            $containerVariable,
            $this->builder->classConstFetch('QuerySerializer', 'class'),
            $this->generateQuerySerializerClosure()
        );
        $statements[] = $this->containerImplementation->registerClosure(
            $containerVariable,
            $this->builder->classConstFetch('ResponseHandler', 'class'),
            $this->generateResponseHandlerClosure($containerVariable)
        );
        $statements[] = $this->containerImplementation->registerClosure(
            $containerVariable,
            $this->builder->classConstFetch('RequestMapperInterface', 'class'),
            $requestMapperClosure
        );
        foreach ($compositeFields as $field) {
            /** @var Field $field */
            $closureStatements = [];
            $mapperClass       = SchemaMapperNaming::getClassName($field);
            $this->addImport(
                sprintf(
                    '%s%s\\%s',
                    $this->baseNamespace,
                    SchemaMapperGenerator::NAMESPACE_SUBPATH,
                    $mapperClass
                )
            );

            $mapperClassConst = $this->builder->classConstFetch($mapperClass, 'class');

            $closureStatements[] = $this->builder->return($this->buildMapperDependencies($field, $containerVariable));

            $closure = $this->builder->closure($closureStatements, [], [$containerVariable], $mapperClass);

            $statements[] = $this->containerImplementation->registerClosure(
                $containerVariable,
                $mapperClassConst,
                $closure
            );
        }

        return $this->builder
            ->method('register')
            ->makePublic()
            ->addParam($param)
            ->addStmts($statements)
            ->composeDocBlock([$param], '', [])
            ->setReturnType(MethodBuilder::RETURN_TYPE_VOID)
            ->getNode();
    }

    private function generateQuerySerializerClosure(): Closure
    {
        return $this->builder->closure(
            [
                $this->builder->return($this->builder->new('QuerySerializer'))
            ],
            [],
            [],
            'QuerySerializer'
        );
    }

    private function generateBodySerializerClosure(Specification $specification): Closure
    {
        $initialStatement = $this->builder->new('BodySerializer');
        $allContentTypes  = $specification->getAllContentTypes();

        if (in_array(JsonContentTypeSerializer::MIME_TYPE, $allContentTypes, true)) {
            $jsonSerializerInit = $this->builder->methodCall(
                $initialStatement,
                'add',
                [
                    $this->builder->new('JsonContentTypeSerializer'),
                ]
            );
        }

        if (in_array(FormUrlencodedContentTypeSerializer::MIME_TYPE, $allContentTypes, true)) {
            $formEncodedSerializerInit = $this->builder->methodCall(
                $jsonSerializerInit ?? $initialStatement,
                'add',
                [
                    $this->builder->new('FormUrlencodedContentTypeSerializer'),
                ]
            );
        }

        if (in_array(XmlContentTypeSerializer::MIME_TYPE, $allContentTypes, true)) {
            $xmlSerializerInit = $this->builder->methodCall(
                $formEncodedSerializerInit ?? $jsonSerializerInit ?? $initialStatement,
                'add',
                [
                    $this->builder->new('XmlContentTypeSerializer'),
                ]
            );
        }

        if (in_array(VdnApiJsonContentTypeSerializer::MIME_TYPE, $allContentTypes, true)) {
            $vdnApiJsonSerializerInit = $this->builder->methodCall(
                $xmlSerializerInit ?? $formEncodedSerializerInit ?? $jsonSerializerInit ?? $initialStatement,
                'add',
                [
                    $this->builder->new('VdnApiJsonContentTypeSerializer'),
                ]
            );
        }

        $registerBodySerializerClosureStatements[] = $this
            ->builder
            ->return($vdnApiJsonSerializerInit ?? $xmlSerializerInit ?? $formEncodedSerializerInit ?? $jsonSerializerInit ?? $initialStatement);

        return $this->builder->closure(
            $registerBodySerializerClosureStatements,
            [],
            [],
            'BodySerializer'
        );
    }

    private function generateResponseHandlerClosure(Variable $containerVariable): Closure
    {
        return $this->builder->closure(
            [
                $this->builder->return(
                    $this->builder->new(
                        'ResponseHandler',
                        [
                            $this->containerImplementation->getClosure(
                                $containerVariable,
                                $this->builder->classConstFetch('BodySerializer', 'class'),
                            ),
                            $this->builder->new('ResponseExceptionFactory'),
                        ]
                    )
                ),
            ],
            [],
            [$containerVariable],
            'ResponseHandler'
        );
    }

    private function buildMapperDependencies(Field $field, Variable $containerVariable): New_
    {
        $dependencies = [];
        if ($field->isObject()) {
            $alreadyInjected = [];
            foreach ($field->getObjectProperties() as $subfield) {
                if ($subfield->isComposite() && !isset($alreadyInjected[$subfield->getPhpClassName()])) {
                    $getMethodArg   = $this->builder->classConstFetch(
                        SchemaMapperNaming::getClassName($subfield),
                        'class'
                    );
                    $dependencies[] = $this->containerImplementation->getClosure($containerVariable, $getMethodArg);

                    $alreadyInjected[$subfield->getPhpClassName()] = true;
                }
            }
        } elseif ($field->isArrayOfObjects()) {
            $getMethodArg   = $this->builder->classConstFetch(
                SchemaMapperNaming::getClassName($field->getArrayItem()),
                'class'
            );
            $dependencies[] = $this->containerImplementation->getClosure($containerVariable, $getMethodArg);
        }

        return $this->builder->new(SchemaMapperNaming::getClassName($field), $dependencies);
    }
}
