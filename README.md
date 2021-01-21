# OpenAPI SDK generator - API client generator

API client generator is a console application capable of auto-generating a [PSR18](https://www.php-fig.org/psr/psr-18/)/[PSR7](https://www.php-fig.org/psr/psr-7/) compliant API client based on [OpenAPI v3](https://swagger.io/specification/) specification according to PHP best practices and your code style standards.

[![Build Status](https://travis-ci.org/DoclerLabs/api-client-generator.svg?branch=master)](https://travis-ci.org/DoclerLabs/api-client-generator)
[![Coverage Status](https://coveralls.io/repos/github/DoclerLabs/api-client-generator/badge.svg?branch=master)](https://coveralls.io/github/DoclerLabs/api-client-generator?branch=master)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)

## Usage
### With Docker
```
$ docker run -it \
-v {path-to-specification}/openapi.yaml:/openapi.yaml:ro \
-v {path-to-client}/some-api-client:/client \
-e NAMESPACE=Group\\SomeApiClient \
-e OPENAPI=/openapi.yaml \
-e OUTPUT_DIR=/client \
-e PACKAGE=group/some-api-client \
dhlabs/api-client-generator
```

### Without Docker
Preconditions: PHP 7.4

Clone the repository and run:
```OPENAPI={path-to-specification}/openapi.yaml NAMESPACE=Group\SomeApiClient PACKAGE=group/some-api-client OUTPUT_DIR={path-to-client}/generated ./bin/api-client-generator generate``` 

## Configuration
The following environment variables are available:

| Variable | Required | Default                             | Enum | Example                    |
|------------|---------|------------------|---------|---------------------------|
| `NAMESPACE` | yes | | | Group\\SomeApiClient |
| `PACKAGE` | yes | | | group/some-api-client |
| `OPENAPI ` | yes | | | /api/openapi.yaml |
| `OUTPUT_DIR` | yes | | | /client |
| `CODE_STYLE` | no | {path-to-repository}/.php_cs.php | | /client/myCodeStyle.php |
| `SOURCE_DIR` | no | src | | src |
| `CLIENT_PHP_VERSION` | no | 7.2 | 7.0, 7.1, 7.2, 7.3, 7.4 | 7.4 |
| `COMPOSER_JSON_TEMPLATE_DIR` | no | {path-to-repository}/template/composer.json.twig | | /path/composer.json.twig |
| `README_MD_TEMPLATE_DIR` | no | {path-to-repository}/template/README.md.twig | | /path/README.md.twig |
| `HTTP_MESSAGE` | no | guzzle | guzzle, nyholm | nyholm |
| `CONTAINER` | no | pimple | pimple | pimple |

## Running tests

```bash
$ composer install
$ make test
```

(check `make` for all available routines).
