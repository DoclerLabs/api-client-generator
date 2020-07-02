# OpenAPI SDK generator - API client generator

API client generator is a console application capable of auto-generating an API client based on OpenAPI specification according to PHP best practices, and your code style standards.

## Usage

### With Docker

#### Build this image

If you have `composer` configured locally to use Satis:

    docker build \
            --build-arg SATIS_USERNAME="$(composer config http-basic.satis-private.doclerholding.com.username)" \
            --build-arg SATIS_PASSWORD="$(composer config http-basic.satis-private.doclerholding.com.password)" \
            -t api-client-generator .

If you don't have `composer` configure locally:

    docker build \
            --build-arg SATIS_USERNAME="YOUR USERNAME" \
            --build-arg SATIS_PASSWORD="YOUR PASSWORD" \
            -t api-client-generator .

#### On the API Client repository

    docker run -it \
      -v `pwd`:/client \
      -v `/absolute/path/to/your-api`:/app:ro \
      api-client-generator

> The following _optional_ environment variables are available:
>
> |Variable    |Default                             |Example                    |
> |------------|------------------------------------|---------------------------|
> |`NAMESPACE` |`<API Namespace>Client`             |`Group\\SomeApiClient`     |
> |`PACKAGE`   |`<API composer package name>-client`|`dh-group/some-api-client` |
> |`OPENAPI `  |`/app/doc/openapi.yaml`             |`/app/web/swagger.yaml`    |
> |`OUTPUT_DIR`|`/client`                           |`/client`                  |
> |`CODE_STYLE`|`/generator/.php_cs.php`            |`/client/myCodeStyle.php`  |

### Without Docker

OPENAPI={path}/swagger.yaml NAMESPACE=Group\SomeApiClient PACKAGE=dh-group/some-api-client OUTPUT_DIR={path}/generated CODE_STYLE={path}/.php_cs.php ./bin/api-client-generator generate 
