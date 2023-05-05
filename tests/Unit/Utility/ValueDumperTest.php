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
        'a', // 1 byte
        '߿', // 2 bytes
        '￿', // 3 bytes
        '𐃮', // 4 bytes
    ];

    public function test_mb_strcut_polyfill(): void
    {
        $base = str_repeat('a', 4);
        foreach (self::UTF8 as $idx => $char) {
            $len = $idx+1;
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

    public function invalid_utf8_data_provider(): \Generator {
        foreach (self::UTF8 as $idx => $char) {
            if (!$idx) continue;
            $len = $idx+1;
            for ($x = 1; $x < $len; $x++) {
                yield [substr($char, 0, $x)];
            }
            yield [substr($char, 0, 1).str_repeat(substr($char, 1), 2)];
        }
    }

    /**
     * @dataProvider invalid_utf8_data_provider
     */
    public function test_invalid_utf8_mb_strcut(string $value): void {
        $this->expectExceptionMessage("An invalid UTF8 value was provided!");

        ValueDumper::cutPolyfillInternal($value, strlen($value), true);
    }
}
