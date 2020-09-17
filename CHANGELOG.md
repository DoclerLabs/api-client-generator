# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [3.0.0] - In progress
### Changed
 - Underlying client implementation is configurable(PSR-18)
 - Most of the static code copied to the client instead of depending on docler-labs/api-client-base
### Added
 - Progress bar added

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
