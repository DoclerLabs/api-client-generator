<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Copy\Serializer\ContentType\Json;

use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\Json\Json;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\Json\JsonException;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testGetLastErrorCodeIsZeroAfterInit()
    {
        self::assertEquals(0, Json::getLastErrorCode());
    }

    public function testGetLastErrorMessageIsNoErrorsAfterInit()
    {
        self::assertEquals('No errors', Json::getLastErrorMessage());
    }

    /**
     * @param int    $errorCode
     * @param string $expectedMessage
     *
     * @dataProvider provideErrorMessagesForErrorCodes
     */
    public function testGetMessageForExistingErrorCodeGivesBackSpecificErrorMessage(
        int $errorCode,
        string $expectedMessage
    ) {
        self::assertEquals($expectedMessage, Json::getErrorMessage($errorCode));
    }

    public function testGetMessageReturnsUnknownErrorForUnknownErrorCode()
    {
        self::assertEquals('Unknown error', Json::getErrorMessage(-1));
    }

    /**
     * @param mixed  $decoded
     * @param string $encoded
     *
     * @dataProvider provideValidData
     *
     * @throws JsonException
     */
    public function testEncodeSuccessfullyWorksForValidInput($decoded, string $encoded)
    {
        self::assertEquals($encoded, Json::encode($decoded));
        self::assertEquals(0, Json::getLastErrorCode());
    }

    /**
     * Encode throws exception on invalid data.
     *
     * @param mixed $invalidInput An invalid input for encode.
     *
     * @dataProvider provideInvalidData
     */
    public function testEncodeThrowsJsonExceptionForInvalidInput($invalidInput)
    {
        $this->expectException(JsonException::class);

        Json::encode($invalidInput);
    }

    /**
     * @param mixed  $decoded
     * @param string $encoded
     *
     * @dataProvider provideValidData
     *
     * @throws JsonException
     */
    public function testDecodeSuccessfullyWorksForValidInput($decoded, string $encoded)
    {
        self::assertEquals($decoded, Json::decode($encoded, true));
        self::assertEquals(0, Json::getLastErrorCode());
    }

    /**
     * @param mixed $invalidInput
     *
     * @dataProvider provideInvalidData
     */
    public function testDecodeThrowsJsonExceptionForInvalidInput($invalidInput)
    {
        $this->expectException(JsonException::class);

        Json::decode($invalidInput);
    }

    /**
     * Decode should keep big int as string if options given.
     */
    public function testDecodeWithJsonBigIntAsStringOptions()
    {
        $decoded = Json::decode('{"bigint": 2057556871673413376}', true, 512, JSON_BIGINT_AS_STRING);

        self::assertSame('2057556871673413376', (string)$decoded['bigint']);
    }

    public function provideValidData(): array
    {
        return [
            'integer'               => [
                'decoded' => 0,
                'encoded' => '0',
            ],
            'string'                => [
                'decoded' => 'a',
                'encoded' => '"a"',
            ],
            'boolean'               => [
                'decoded' => true,
                'encoded' => 'true',
            ],
            'empty array'           => [
                'decoded' => [],
                'encoded' => '[]',
            ],
            'array with string key' => [
                'decoded' => ['a' => 2],
                'encoded' => '{"a":2}',
            ],
            'deep array'            => [
                'decoded' => [2 => ['b' => 'c', 'd' => 3]],
                'encoded' => '{"2":{"b":"c","d":3}}',
            ],
        ];
    }

    public function provideInvalidData(): array
    {
        return [
            ["\xC3\x2E"],
        ];
    }

    public function provideErrorMessagesForErrorCodes(): array
    {
        return [
            'no errors'         => [
                'errorCode'       => JSON_ERROR_NONE,
                'expectedMessage' => 'No errors',
            ],
            'stack depth'       => [
                'errorCode'       => JSON_ERROR_DEPTH,
                'expectedMessage' => 'Maximum stack depth exceeded',
            ],
            'state mismatch'    => [
                'errorCode'       => JSON_ERROR_STATE_MISMATCH,
                'expectedMessage' => 'Underflow or the modes mismatch',
            ],
            'control character' => [
                'errorCode'       => JSON_ERROR_CTRL_CHAR,
                'expectedMessage' => 'Unexpected control character found',
            ],
            'syntax error'      => [
                'errorCode'       => JSON_ERROR_SYNTAX,
                'expectedMessage' => 'Syntax error, malformed JSON',
            ],
            'utf8 error'        => [
                'errorCode'       => JSON_ERROR_UTF8,
                'expectedMessage' => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            ],
            // Error codes for PHP version >= 5.5.0
            'recursion'         => [
                'errorCode'       => JSON_ERROR_RECURSION,
                'expectedMessage' => 'Recursive reference was found',
            ],
            'nan or inf'        => [
                'errorCode'       => JSON_ERROR_INF_OR_NAN,
                'expectedMessage' => 'NaN or Inf was found',
            ],
            'unsupported type'  => [
                'errorCode'       => JSON_ERROR_UNSUPPORTED_TYPE,
                'expectedMessage' => 'Unsupported type was found',
            ],
        ];
    }
}
