<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility;

use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Utility\ValueDumper;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ValueDumperTest extends TestCase
{
    /**
     * @dataProvider dump_value_returns_correct_signature_data_provider
     */
    public function test_dump_value_returns_correct_signature(mixed $value, string $expected): void
    {
        self::assertSame($expected, ValueDumper::dump($value));
    }

    public function dump_value_returns_correct_signature_data_provider(): array
    {
        return [
            'null' => [null, 'null'],
            'boolean true' => [true, 'true'],
            'boolean false' => [false, 'false'],
            'integer' => [42, '42'],
            'float' => [1337.404, '1337.404'],
            'string with single quote' => ['foo', "'foo'"],
            'string with double quote' => ["foo'bar", '"foo\'bar"'],
            'string with both quotes' => ['"foo\'bar"', '\'"foo\\\'bar"\''],
            'string with exact max length' => ['Lorem ipsum dolor sit amet, consectetur adipiscing', "'Lorem ipsum dolor sit amet, consectetur adipiscing'"],
            'string cropped' => ['Lorem ipsum dolor sit amet, consectetur adipiscing elit.', "'Lorem ipsum dolor sit amet, consectetur adipiscing…'"],
            'utf8 string cropped' => ['🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄', "'🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄…'"],
            'string cropped only after threshold' => ['Lorem12345 ipsumdolorsitamet,consecteturadipiscingelit.Curabitur', "'Lorem12345 ipsumdolorsitamet,consecteturadipiscinge…'"],
            'string without space cropped' => ['Loremipsumdolorsitamet,consecteturadipiscingelit.Curabitur',"'Loremipsumdolorsitamet,consecteturadipiscingelit.Cu…'"],
            'date' => [new DateTimeImmutable('@1648733888'), '2022/03/31 13:38:08'],
            'object' => [new stdClass(), 'object(stdClass)'],
            'array' => [['foo' => 'bar', 'baz'], "array{foo: 'bar', 0: 'baz'}"],
            'array with in-depth entries' => [['foo' => ['bar' => 'baz']], "array{foo: array{…}}"],
            'array with too much entries' => [[0, 1, 2, 3, 4, 5, 6, 7], "array{0: 0, 1: 1, 2: 2, 3: 3, 4: 4, 5: 5, …}"],
        ];
    }

    /**
     * PHP8.1 move to data provider
     *
     * @requires PHP >= 8.1
     */
    public function test_dump_enum_value_returns_correct_signature(): void
    {
        self::assertSame("'FOO'", ValueDumper::dump(PureEnum::FOO));
        self::assertSame("'foo'", ValueDumper::dump(BackedStringEnum::FOO));
        self::assertSame('42', ValueDumper::dump(BackedIntegerEnum::FOO));
    }

    private const UTF8 = [
        ['a'], // 1 byte
        ["\u{07FF}"], // 2 bytes
        ["\u{FFFF}"], // 3 bytes
        ["\u{10FFFD}", "\u{90000}", "\u{40000}"], // 4 bytes
    ];

    public function test_mb_strcut_polyfill(): void
    {
        $base = str_repeat('a', 4);
        foreach (self::UTF8 as $idx => $chars) {
            $len = $idx+1;
            foreach ($chars as $char) {
                self::assertEquals($len, strlen($char));

                for ($x = 0; $x <= $len; $x++) {
                    $cur = substr($base, $x);
                    if ($x === $len) {
                        $cur .= $char;
                    }
                    self::assertSame($cur, ValueDumper::cutPolyfill($cur.$char, 4));
                }
            }
        }
        self::assertSame('', ValueDumper::cutPolyfill('test', 0));
    }
    public function test_invalid_utf8(): void
    {
        // Invalid utf8 values are trimmed, if present at the end of the string
        // (really just an edge case we shouldn't care about)

        $base = "\u{07FF}";
        $trailer = substr($base, 1);
        self::assertSame('', ValueDumper::cutPolyfill($trailer, 10));
        self::assertSame('', ValueDumper::cutPolyfill($base.$trailer, 10));
        self::assertSame('', ValueDumper::cutPolyfill($base.$trailer.$trailer, 10));
        self::assertSame('', ValueDumper::cutPolyfill($base.$trailer.$trailer.$trailer, 10));

        self::assertSame('', ValueDumper::cutPolyfill(substr($base, 0, 1), 10));
    }
}
