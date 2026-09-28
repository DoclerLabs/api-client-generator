<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\RequestGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\RequestGenerator
 */
class RequestGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Request with body with php 7.4 + api key in header' => [
                '/Request/patchResource.yaml',
                '/Request/PatchResourceRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchResourceRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with body with php 8.0 + api key in header' => [
                '/Request/patchResource.yaml',
                '/Request/PatchResourceRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with body with php 8.1 + api key in header' => [
                '/Request/patchResource.yaml',
                '/Request/PatchResourceRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with body with php 8.3 + api key in header' => [
                '/Request/patchResource.yaml',
                '/Request/PatchResourceRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with body with php 7.4 + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchSubResourceRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchSubResourceRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with body with php 8.0 + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchSubResourceRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with body with php 8.1 + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchSubResourceRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with body with php 8.3 + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchSubResourceRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with body with php 7.4 + query params + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchYetAnotherSubResourceRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchYetAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with body with php 8.0 + query params + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchYetAnotherSubResourceRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchYetAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with body with php 8.1 + query params + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchYetAnotherSubResourceRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchYetAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with body with php 8.3 + query params + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchYetAnotherSubResourceRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchYetAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with body with php 7.4 + api key in cookie' => [
                '/Request/patchResource.yaml',
                '/Request/PatchAnotherSubResourceRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with body with php 8.0 + api key in cookie' => [
                '/Request/patchResource.yaml',
                '/Request/PatchAnotherSubResourceRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with body with php 8.1 + api key in cookie' => [
                '/Request/patchResource.yaml',
                '/Request/PatchAnotherSubResourceRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with body with php 8.3 + api key in cookie' => [
                '/Request/patchResource.yaml',
                '/Request/PatchAnotherSubResourceRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with mandatory parameters and body with php 7.4' => [
                '/Request/putResourceById.yaml',
                '/Request/PutResourceByIdRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PutResourceByIdRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with mandatory parameters and body with php 8.0' => [
                '/Request/putResourceById.yaml',
                '/Request/PutResourceByIdRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PutResourceByIdRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with mandatory parameters and body with php 8.1' => [
                '/Request/putResourceById.yaml',
                '/Request/PutResourceByIdRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PutResourceByIdRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with mandatory parameters and body with php 8.3' => [
                '/Request/putResourceById.yaml',
                '/Request/PutResourceByIdRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PutResourceByIdRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request without mandatory parameters and body with php 7.4' => [
                '/Request/getResources.yaml',
                '/Request/GetResourcesRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request without mandatory parameters and body with php 8.0' => [
                '/Request/getResources.yaml',
                '/Request/GetResourcesRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request without mandatory parameters and body with php 8.1' => [
                '/Request/getResources.yaml',
                '/Request/GetResourcesRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request without mandatory parameters and body with php 8.3' => [
                '/Request/getResources.yaml',
                '/Request/GetResourcesRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with same parameter name but different parameters with php 7.4' => [
                '/Request/getResources.yaml',
                '/Request/GetSubResourcesRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetSubResourcesRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with same parameter name but different parameters with php 8.0' => [
                '/Request/getResources.yaml',
                '/Request/GetSubResourcesRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetSubResourcesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with same parameter name but different parameters with php 8.1' => [
                '/Request/getResources.yaml',
                '/Request/GetSubResourcesRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetSubResourcesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with same parameter name but different parameters with php 8.3' => [
                '/Request/getResources.yaml',
                '/Request/GetSubResourcesRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetSubResourcesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with enum path parameter and php 7.4' => [
                '/Request/getResourceByType.yaml',
                '/Request/GetResourceByTypeRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourceByTypeRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'Request with enum path parameter and php 8.0' => [
                '/Request/getResourceByType.yaml',
                '/Request/GetResourceByTypeRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourceByTypeRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with enum path parameter and php 8.1' => [
                '/Request/getResourceByType.yaml',
                '/Request/GetResourceByTypeRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourceByTypeRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with enum path parameter and php 8.3' => [
                '/Request/getResourceByType.yaml',
                '/Request/GetResourceByTypeRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourceByTypeRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with content type enum header parameters and body with php 7.4' => [
                '/Request/postResourceById.yaml',
                '/Request/PostResourceByIdRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PostResourceByIdRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with content type enum header parameters and body with php 8.0' => [
                '/Request/postResourceById.yaml',
                '/Request/PostResourceByIdRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PostResourceByIdRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with content type enum header parameters and body with php 8.1' => [
                '/Request/postResourceById.yaml',
                '/Request/PostResourceByIdRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PostResourceByIdRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with content type enum header parameters and body with php 8.3' => [
                '/Request/postResourceById.yaml',
                '/Request/PostResourceByIdRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PostResourceByIdRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with mandatory and optional array of enums query parameters with php 7.4' => [
                '/Request/getResourcesByStatuses.yaml',
                '/Request/GetResourcesByStatusesRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesByStatusesRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with mandatory and optional array of enums query parameters with php 8.1' => [
                '/Request/getResourcesByStatuses.yaml',
                '/Request/GetResourcesByStatusesRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesByStatusesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with mandatory and optional array of enums query parameters with php 8.3' => [
                '/Request/getResourcesByStatuses.yaml',
                '/Request/GetResourcesByStatusesRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesByStatusesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with nullable optional parameters with php 7.2' => [
                '/Request/findItems.yaml',
                '/Request/FindItemsRequest72.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\FindItemsRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP72)->build(),
            ],
            'Request with nullable optional parameters with php 7.4' => [
                '/Request/findItems.yaml',
                '/Request/FindItemsRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\FindItemsRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with nullable optional parameters with php 8.1' => [
                '/Request/findItems.yaml',
                '/Request/FindItemsRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\FindItemsRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with nullable optional parameters with php 8.3' => [
                '/Request/findItems.yaml',
                '/Request/FindItemsRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\FindItemsRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with a body media type without schema with php 7.2' => [
                '/Request/checkEmailStatus.yaml',
                '/Request/CheckEmailStatusRequest72.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\CheckEmailStatusRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP72)->build(),
            ],
            'Request with a body media type without schema with php 7.4' => [
                '/Request/checkEmailStatus.yaml',
                '/Request/CheckEmailStatusRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\CheckEmailStatusRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with a body media type without schema with php 8.1' => [
                '/Request/checkEmailStatus.yaml',
                '/Request/CheckEmailStatusRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\CheckEmailStatusRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with a body media type without schema with php 8.3' => [
                '/Request/checkEmailStatus.yaml',
                '/Request/CheckEmailStatusRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\CheckEmailStatusRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with a body media type without schema next to one with schema with php 7.2' => [
                '/Request/checkEmailStatus.yaml',
                '/Request/CreateNoteRequest72.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\CreateNoteRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP72)->build(),
            ],
            'Request with a body media type without schema next to one with schema with php 7.4' => [
                '/Request/checkEmailStatus.yaml',
                '/Request/CreateNoteRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\CreateNoteRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with a body media type without schema next to one with schema with php 8.1' => [
                '/Request/checkEmailStatus.yaml',
                '/Request/CreateNoteRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\CreateNoteRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with a body media type without schema next to one with schema with php 8.3' => [
                '/Request/checkEmailStatus.yaml',
                '/Request/CreateNoteRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\CreateNoteRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with integer, number and boolean header parameters with php 7.2' => [
                '/Request/getMessages.yaml',
                '/Request/GetMessagesRequest72.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetMessagesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP72)->build(),
            ],
            'Request with integer, number and boolean header parameters with php 7.4' => [
                '/Request/getMessages.yaml',
                '/Request/GetMessagesRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetMessagesRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with integer, number and boolean header parameters with php 8.1' => [
                '/Request/getMessages.yaml',
                '/Request/GetMessagesRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetMessagesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with integer, number and boolean header parameters with php 8.3' => [
                '/Request/getMessages.yaml',
                '/Request/GetMessagesRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetMessagesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with literal integer body with php 7.2' => [
                '/Request/addMember.yaml',
                '/Request/AddMemberRequest72.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\AddMemberRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP72)->build(),
            ],
            'Request with literal integer body with php 7.4' => [
                '/Request/addMember.yaml',
                '/Request/AddMemberRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\AddMemberRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with literal integer body with php 8.1' => [
                '/Request/addMember.yaml',
                '/Request/AddMemberRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\AddMemberRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with literal integer body with php 8.3' => [
                '/Request/addMember.yaml',
                '/Request/AddMemberRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\AddMemberRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with literal nullable string body with php 7.4' => [
                '/Request/addMember.yaml',
                '/Request/SetNicknameRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\SetNicknameRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with literal nullable string body with php 8.3' => [
                '/Request/addMember.yaml',
                '/Request/SetNicknameRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\SetNicknameRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with array of strings body with php 7.4' => [
                '/Request/addMember.yaml',
                '/Request/SetTagsRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\SetTagsRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with array of strings body with php 8.3' => [
                '/Request/addMember.yaml',
                '/Request/SetTagsRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\SetTagsRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with literal date-time body with php 7.4' => [
                '/Request/addMember.yaml',
                '/Request/SetExpiryRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\SetExpiryRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with literal date-time body with php 8.3' => [
                '/Request/addMember.yaml',
                '/Request/SetExpiryRequest83.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\SetExpiryRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Request with optional untyped (mixed) query parameter with php 7.4' => [
                '/Request/getResourcesByUntypedFilter.yaml',
                '/Request/GetResourcesByUntypedFilterRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesByUntypedFilterRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with optional untyped (mixed) query parameter with php 8.0' => [
                '/Request/getResourcesByUntypedFilter.yaml',
                '/Request/GetResourcesByUntypedFilterRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesByUntypedFilterRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with enum values consisting of symbols only with php 7.4' => [
                '/Request/getResourcesBySymbols.yaml',
                '/Request/GetResourcesBySymbolsRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesBySymbolsRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with enum values consisting of symbols only with php 8.1' => [
                '/Request/getResourcesBySymbols.yaml',
                '/Request/GetResourcesBySymbolsRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesBySymbolsRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request with enum query parameters whose class names collide with php 8.1' => [
                '/Schema/classNameCollisions.yaml',
                '/Request/FindHostsRequest81.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\FindHostsRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return RequestGenerator::class;
    }
}
