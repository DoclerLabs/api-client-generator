# Contributing

Thanks for checking out this OpenAPI Client Generator! We look forward to your contributions!

## Types of contributions we're looking for

There are many ways you can contribute:

* Improve documentation
* Write tests
* Add missing features from OpenAPI specification

## Code of Conduct

Please note that this project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project you agree to abide by its terms.

## How to contribute

If you'd like to contribute, start by searching through the [issues](https://github.com/DoclerLabs/api-client-generator/issues) and [pull requests](https://github.com/DoclerLabs/api-client-generator/pulls) to see whether someone else has raised a similar idea or question.

If you don't see your idea listed, and you think it fits into the goals of this guide, do one of the following:
* **If your contribution is minor,** such as a typo fix, open a pull request.
* **If your contribution is major,** such as a new feature, start by opening an issue first. That way, other people can weigh in on the discussion before you do any work.

If you're opening a PR, keep in mind Github will validate code style (PSR1/PSR2) and tests / coverage. Also, don't forget to update [CHANGELOG.md](https://github.com/DoclerLabs/api-client-generator/blob/master/CHANGELOG.md) and documentation accordingly.

## Setting up your environment

This generator works with PHP 7.4 so either you need to have it locally installed or you can use any php 7.4 docker image to run phpunit, phpstan, php-cs-fixer etc.

To make sure your code is following the correct style, run (or check `make` to run these tasks through php 7.4 docker image):

```
$ ./vendor/bin/php-cs-fixer fix .
```

To statically analyse your code, run:

```
$ ./vendor/bin/phpstan analyse src
```

To run tests:

```
$ ./vendor/bin/phpunit
```

After doing your changes, you can validate it by using your modified generator with an actual Open API specification file. To do that, you must generate your own local Docker image of this generator and use it with any spec you may have:

```
$ docker build -t my-api-client-generator .
```

Then run the same command to build your API client but replacing `dhlabs/api-client-generator` with `my-api-client-generator`. For example:
```
docker run -it \
-v {path-to-specification}/openapi.yaml:/openapi.yaml:ro \
-v {path-to-client}/some-api-client:/client \
-e NAMESPACE=Group\\SomeApiClient \
-e OPENAPI=/openapi.yaml \
-e OUTPUT_DIR=/client \
-e PACKAGE=group/some-api-client \
my-api-client-generator
```

## Community

Discussions must take place on this repository's [Issues](https://github.com/DoclerLabs/api-client-generator/issues) and [Pull Requests](https://github.com/DoclerLabs/api-client-generator/pulls) sections. Anybody is welcome to join these conversations.

Wherever possible, do not take these conversations to private channels, including contacting the maintainers directly. Keeping communication public means everybody can benefit and learn from the conversation.
