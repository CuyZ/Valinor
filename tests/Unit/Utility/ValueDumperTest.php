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
            'pure enum' => [PureEnum::FOO, "'FOO'"],
            'backed string enum' => [BackedStringEnum::FOO, "'foo'"],
            'backed integer enum' => [BackedIntegerEnum::FOO, '42'],
            'date' => [new DateTimeImmutable('@1648733888'), '2022/03/31 13:38:08'],
            'object' => [new stdClass(), 'object(stdClass)'],
            'array' => [['foo' => 'bar', 'baz'], "array{foo: 'bar', 0: 'baz'}"],
            'array with in-depth entries' => [['foo' => ['bar' => 'baz']], "array{foo: array{…}}"],
            'array with too much entries' => [[0, 1, 2, 3, 4, 5, 6, 7], "array{0: 0, 1: 1, 2: 2, 3: 3, 4: 4, 5: 5, …}"],
        ];
    }
}
