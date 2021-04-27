# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## [5.4.0] - 2021-04-22
### Added
 - Support for `license` info. Use it in the default composer.json tpl.

## [5.3.0] - 2021-04-13
### Added
 - Support for `mixed` parameter type
 - Support for anyOf `mixed` parameter type

### Fixed
 - `mixed` arrays rendered incorrectly
 - Wrong typehint in case of nullable arrays

## [5.2.0] - 2021-04-06
### Added
 - Support for `minimum`, `maximum`, `exclusiveMinimum`, `exclusiveMaximum`, `minItems`, `maxItems`, `pattern`, `maxLength`, `minLength` validations

### Changed
 - Example generated with 7.4

## [5.1.0] - 2021-04-02
### Changed
 - Only content type serializers which are used are included

## [5.0.0] - 2021-02-15
### Changed
 - By default, the PHP version used in the generated client is 7.4.

## [4.2.0] - 2021-01-24
- Acceptance test to generate example from canonical pet store OpenApi specification
- Content-type argument if multiple specified for request
### Fixed
- Literal type JSON response body
### Changed
- Multiple content-type per request and response with the same schema allowed
- Changed non-existing successful response error to a warning


## [4.1.0] - 2020-12-28
### Fixed
 - Nullable mandatory DateTime properties in schema now work correctly without doing `new DateTimeImmutable(null)`
 - Proper nullable typehint and default value in properties for 7.4 schemas to avoid non initialized errors

### Changed
 - Changed default php-cs-fixer configuration to remove superfluous docblocks, to improve generated code readability

### Added
 - Makefile with standard routines to easy development / contribution (run tests, analyse code, fix code style)

## [4.0.0] - 2020-11-26
### Changed
 - Guzzle 6/7 dependency removed altogether in favor of PSR-18 interfaces for Http Client
 - Breaking change! `*ClientFactory` does not accept URI and Guzzle configuration anymore, but rather PSR-18 implementation
 - Guzzle 6/7 CookieJar dependency removed in favor of simplified CookieJar class

## [3.2.1] - 2020-11-24
### Fixed
- `DateTimeInterface` serialization in `QuerySerializer`

## [3.2.0] - 2020-11-10
### Added
 - Query parameters which implement `JsonSerializable` will be converted accordingly.

## [3.1.0] - 2020-11-05
### Fixed
 - Fix collections `toArray` docblock

### Added
 - Schemas implement `JsonSerializable` to be easily encoded with native `json_encode`

## [3.0.6] - 2020-10-22
### Fixed
 - Fix/improve Progress Bar output

## [3.0.5] - 2020-10-16
### Fixed
 - Collection methods use property `items` directly, without calling `toArray`

## [3.0.4] - 2020-10-16
### Fixed
- Fix `Content-type` header handling.

## [3.0.3] - 2020-10-15
### Fixed
 - Serialize items from collection manually, since `json_encode` won't call `toArray` automatically as it used to do with `jsonSerialize`

## [3.0.2] - 2020-10-08
### Fixed
 - Send `RequestInterface` instance to Guzzle instead of `ServerRequestInterface`

## [3.0.1] - 2020-10-08
### Fixed
 - Request object is immutable so must be assigned back when using `with` methods

## [3.0.0] - 2020-09-29
### Added
 - Progress bar added
 - PHP version dependant syntax resolution
 - PHP version 7.2, 7.3 support
 - Service provider generator added
 - Content-type property added to the Request
 - Request mapper generator added
 - Specification file is copied to the client directory
### Changed
 - Breaking change! getResponse request renamed to sendRequest and return PSR7 ResponseInterface instead of DoclerLabs\ApiClientBase\Response\Response
 - Most of the static code copied to the client instead of depending on docler-labs/api-client-base
 - Headers removed from default Guzzle config
 - Http client implementation abstracted behind PSR-17 interface
 - Http message implementation abstracted behind PSR-7 interface
 - Container implementation abstracted behind PSR-11 interface
 - Source directory name is configurable
 - Json and form-encoded serializers instead of built-in json
 - SerializableInterface added
 - ResponseMapper renamed to SchemaMapper
 - Client dependencies initialization moved to service provider
 - OperationId is no longer mandatory
### Fixed
 - CS Fixer fails on invalid generated PHP file
 - Nullable fields schema mapper fix
 - Naming collisions in embedded objects handled

## [2.0.0] - 2020-08-31
### Changed
 - docler-labs/api-client-base updated to 3.0.0
### Removed
 - Response 'data' key handling from `Response`.

## [1.2.0] - 2020-07-30
### Changed
 - docler-labs/api-client-base updated to 2.0.0
### Added
 - The `getResponse` method exposed in the generated Client class. Could be used to retrieve response headers. 

## [1.1.0] - 2020-07-21
### Added
 - README.md meta template
### Changed
 - Any number of allOf schemas allowed
 - allOf schemas joined to one schema without inheritance
 - Response mapper invocation reused in the client factory 
### Fixed
 - Empty object handling
 
## [1.0.1] - 2020-07-16
### Fixed
 - Fix for date and date-time fields parsing
 
## [1.0.0] - 2020-07-09
### Added
 - Initial API client generator release
