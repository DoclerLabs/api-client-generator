<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Naming;

use DoclerLabs\ApiClientGenerator\Naming\CaseCaster;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Naming\CaseCaster
 */
class CaseCasterTest extends TestCase
{
    /**
     * @dataProvider snakeCaseDataProvider
     */
    public function testToSnake(string $input, string $expected)
    {
        self::assertEquals($expected, CaseCaster::toSnake($input));
    }

    /**
     * @dataProvider camelCaseDataProvider
     */
    public function testToCamel(string $input, string $expected)
    {
        self::assertEquals($expected, CaseCaster::toCamel($input));
    }

    /**
     * @dataProvider snakeCaseDataProvider
     */
    public function testToMacro(string $input, string $expected)
    {
        self::assertEquals(strtoupper($expected), CaseCaster::toMacro($input));
    }

    /**
     * @dataProvider camelCaseDataProvider
     */
    public function testToPascal(string $input, string $expected)
    {
        self::assertEquals(ucfirst($expected), CaseCaster::toPascal($input));
    }

    public function camelCaseDataProvider()
    {
        return [
            [
                "f",
                "f",
            ],
            [
                "1234",
                "1234",
            ],
            [
                "FOO",
                "foo",
            ],
            [
                "FooBar",
                "fooBar",
            ],
            [
                "fooBar",
                "fooBar",
            ],
            [
                "foo bar",
                "fooBar",
            ],
            [
                "Foo - Bar",
                "fooBar",
            ],
            [
                "foo & bar",
                "fooBar",
            ],
            [
                "FooFooBar",
                "fooFooBar",
            ],
            [
                "Foo2Foo2Bar",
                "foo2Foo2Bar",
            ],
            [
                "foo-bar-baz",
                "fooBarBaz",
            ],
            [
                "foo_bar_1_2",
                "fooBar12",
            ],
            [
                "_foo_bar_baz_",
                "fooBarBaz",
            ],
            [
                "--foo-bar--",
                "fooBar",
            ],
            [
                "FOO_BAR_baz",
                "fooBarBaz",
            ],
            [
                "__FOO_BAR__",
                "fooBar",
            ],
            [
                "Foo w1th apo's and punc]t",
                "fooW1thApoSAndPuncT",
            ],
            [
                "getHTTPResponse",
                "getHttpResponse",
            ],
            [
                "currencyISOCode",
                "currencyIsoCode",
            ],
            [
                "get2HTTPResponse",
                "get2HttpResponse",
            ],
            [
                "HTTPResponseCode",
                "httpResponseCode",
            ],
            [
                "HTTPResponseCodeXY",
                "httpResponseCodeXy",
            ],
        ];
    }

    public function snakeCaseDataProvider()
    {
        return [
            [
                "",
                "",
            ],
            [
                "f",
                "f",
            ],
            [
                "1234",
                "1234",
            ],
            [
                "FOO",
                "foo",
            ],
            [
                "FooBar",
                "foo_bar",
            ],
            [
                "fooBar",
                "foo_bar",
            ],
            [
                "foo bar",
                "foo_bar",
            ],
            [
                "Foo - Bar",
                "foo_bar",
            ],
            [
                "foo & bar",
                "foo_bar",
            ],
            [
                "FooFooBar",
                "foo_foo_bar",
            ],
            [
                "Foo2Foo2Bar",
                "foo2_foo2_bar",
            ],
            [
                "foo-bar-baz",
                "foo_bar_baz",
            ],
            [
                "foo_bar_1_2",
                "foo_bar_1_2",
            ],
            [
                "_foo_bar_baz_",
                "foo_bar_baz",
            ],
            [
                "--foo-bar--",
                "foo_bar",
            ],
            [
                "FOO_BAR_baz",
                "foo_bar_baz",
            ],
            [
                "__FOO_BAR__",
                "foo_bar",
            ],
            [
                "Foo w1th apo's and punc]t",
                "foo_w1th_apo_s_and_punc_t",
            ],
            [
                "getHTTPResponse",
                "get_http_response",
            ],
            [
                "currencyISOCode",
                "currency_iso_code",
            ],
            [
                "get2HTTPResponse",
                "get2_http_response",
            ],
            [
                "HTTPResponseCode",
                "http_response_code",
            ],
            [
                "HTTPResponseCodeXY",
                "http_response_code_xy",
            ],
        ];
    }
}
