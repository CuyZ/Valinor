<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\String;

use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\String\StringFormatterError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

use function ini_get;
use function ini_set;

final class StringFormatterTest extends UnitTestCase
{
    #[RequiresPhpExtension('intl')]
    public function test_wrong_intl_format_throws_exception(): void
    {
        $this->expectException(StringFormatterError::class);
        $this->expectExceptionMessage('Message formatter error using `some {wrong.format}`');

        StringFormatter::format('en', 'some {wrong.format}', []);
    }

    #[RequiresPhpExtension('intl')]
    public function test_wrong_intl_format_throws_exception_with_intl_exception(): void
    {
        $oldIni = ini_get('intl.use_exceptions');
        try {
            ini_set('intl.use_exceptions', '1');
            $this->expectException(StringFormatterError::class);
            $this->expectExceptionMessage('Message formatter error using `some {wrong.format}`');

            StringFormatter::format('en', 'some {wrong.format}', []);
        } finally {
            ini_set('intl.use_exceptions', $oldIni);
        }
    }

    #[DataProvider('invalid_utf8_parameter_data_provider')]
    public function test_invalid_utf8_parameters_are_replaced(string $value, string $expected): void
    {
        self::assertSame($expected, StringFormatter::format('en', '{value}', ['value' => $value]));
    }

    public static function invalid_utf8_parameter_data_provider(): array
    {
        return [
            'invalid byte' => ["\xB1\x31", '�1'],
            'truncated sequence' => ["\xE2\x82", '�'],
            'valid unicode' => ['été 🦄', 'été 🦄'],
            'mixed unicode and invalid byte' => ["été 🦄\xB1", 'été 🦄�'],
            'empty string' => ['', ''],
        ];
    }

    #[RequiresPhpExtension('intl')]
    #[DataProvider('invalid_message_body_with_invalid_utf8_parameter_data_provider')]
    public function test_invalid_message_body_still_throws_with_invalid_utf8_parameter(string $intlExceptions, string $body): void
    {
        $oldIni = ini_get('intl.use_exceptions');

        try {
            ini_set('intl.use_exceptions', $intlExceptions);
            $this->expectException(StringFormatterError::class);

            StringFormatter::format('en', $body, ['value' => "\xB1\x31"]);
        } finally {
            ini_set('intl.use_exceptions', $oldIni);
        }
    }

    public static function invalid_message_body_with_invalid_utf8_parameter_data_provider(): array
    {
        return [
            ['0', 'some {wrong.format}'],
            ['1', 'some {wrong.format}'],
            ['0', "some \xB1 {value}"],
            ['1', "some \xB1 {value}"],
        ];
    }

    public function test_wrong_message_body_format_throws_exception(): void
    {
        $this->expectException(StringFormatterError::class);
        $this->expectExceptionMessage('Message formatter error using `some message with {invalid format}`');

        StringFormatter::format('en', 'some message with {invalid format}');
    }
}
