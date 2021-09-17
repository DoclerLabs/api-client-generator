<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Copy\Request;

use DoclerLabs\ApiClientGenerator\Output\Copy\Request\CookieJar;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\Copy\Request\CookieJar
 */
class CookieJarTest extends TestCase
{
    /**
     * @dataProvider validCookiesProvider
     */
    public function testCanAddCookiesToPsrRequest(array $cookies, string $serializedCookie)
    {
        $cookieJar = new CookieJar($cookies);

        /** @var RequestInterface|MockObject $requestWithCookies */
        $requestWithCookies = $this->createMock(RequestInterface::class);

        /** @var RequestInterface|MockObject $request */
        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects(self::once())
            ->method('withHeader')
            ->with('Cookie', $serializedCookie)
            ->willReturn($requestWithCookies);

        self::assertSame($requestWithCookies, $cookieJar->withCookieHeader($request));
    }

    public function testWillNotChangeRequestIfThereAreNoCookies()
    {
        $cookieJar = new CookieJar([]);

        /** @var RequestInterface|MockObject */
        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects(self::never())
            ->method('withHeader');

        $cookieJar->withCookieHeader($request);
    }

    /**
     * @dataProvider invalidCookiesProvider
     */
    public function testCannotAddCookiesToPsrRequest(array $cookies)
    {
        $this->expectException(InvalidArgumentException::class);

        new CookieJar($cookies);
    }

    public function validCookiesProvider(): array
    {
        return [
            'regular cookie'          => [
                'cookies'          => ['foo' => 'bar'],
                'serializedCookie' => 'foo=bar'
            ],
            'multiple cookies'        => [
                'cookies'          => ['foo' => 'bar', 'foobar' => 'barfoo'],
                'serializedCookie' => 'foo=bar; foobar=barfoo'
            ],
            'with integers as values' => [
                'cookies'          => ['foo' => '0', 'bar' => 123, 'foobar' => 0],
                'serializedCookie' => 'foo=0; bar=123; foobar=0'
            ],
        ];
    }

    public function invalidCookiesProvider(): array
    {
        return [
            'with spaces on name'  => [
                'cookies' => ['foo bar' => 'bar'],
            ],
            'with newline on name' => [
                'cookies' => ["foo\n" => 'bar'],
            ],
            'with empty value'     => [
                'cookies' => ['foo' => ''],
            ],
        ];
    }
}
