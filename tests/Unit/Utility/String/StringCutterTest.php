<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\String;

use CuyZ\Valinor\Utility\String\StringCutter;
use PHPUnit\Framework\TestCase;

final class StringCutterTest extends TestCase
{
    /**
     * @dataProvider mb_strcut_polyfill_data_provider
     */
    public function test_mb_strcut_polyfill(string $base, int $length, string $expected): void
    {
        $cut = StringCutter::cutPolyfill($base, $length);

        self::assertSame($expected, $cut);
    }

    public function mb_strcut_polyfill_data_provider(): iterable
    {
        yield '1 byte' => [
            'base' => 'foobar',
            'length' => 3,
            'expected' => 'foo',
        ];

        yield '2 bytes not cut' => [
            'base' => "foo\u{07FF}bar",
            'length' => 5,
            'expected' => "foo\u{07FF}",
        ];

        yield '2 bytes cut' => [
            'base' => "foo\u{07FF}",
            'length' => 4,
            'expected' => 'foo',
        ];

        yield '3 bytes not cut' => [
            'base' => "foo\u{FFFF}bar",
            'length' => 6,
            'expected' => "foo\u{FFFF}",
        ];

        yield '3 bytes cut' => [
            'base' => "foo\u{FFFF}bar",
            'length' => 5,
            'expected' => 'foo',
        ];

        yield '4 bytes not cut #1' => [
            'base' => "foo\u{10FFFD}bar",
            'length' => 7,
            'expected' => "foo\u{10FFFD}",
        ];

        yield '4 bytes cut #1' => [
            'base' => "foo\u{10FFFD}bar",
            'length' => 6,
            'expected' => 'foo',
        ];

        yield '4 bytes not cut #2' => [
            'base' => "foo\u{90000}bar",
            'length' => 7,
            'expected' => "foo\u{90000}",
        ];

        yield '4 bytes not cut #3' => [
            'base' => "foo\u{40000}bar",
            'length' => 7,
            'expected' => "foo\u{40000}",
        ];

        yield '4 bytes #4' => [
            'base' => "foo🦄bar",
            'length' => 7,
            'expected' => "foo🦄",
        ];

        yield '4 bytes cut #4' => [
            'base' => "foo🦄bar",
            'length' => 6,
            'expected' => 'foo',
        ];
    }

    public function test_invalid_utf8(): void
    {
        // Invalid utf8 values are trimmed, if present at the end of the string
        // (really just an edge case we shouldn't care about)

        $base = "\u{07FF}";
        $trailer = substr($base, 1);
        self::assertSame('', StringCutter::cutPolyfill($trailer, 10));
        self::assertSame('', StringCutter::cutPolyfill($base . $trailer, 10));
        self::assertSame('', StringCutter::cutPolyfill($base . $trailer . $trailer, 10));
        self::assertSame('', StringCutter::cutPolyfill($base . $trailer . $trailer . $trailer, 10));

        self::assertSame('', StringCutter::cutPolyfill(substr($base, 0, 1), 10));
    }
}
