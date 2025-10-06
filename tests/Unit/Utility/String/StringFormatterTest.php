<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\String;

use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\String\StringFormatterError;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

final class StringFormatterTest extends TestCase
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

    public function test_wrong_message_body_format_throws_exception(): void
    {
        $this->expectException(StringFormatterError::class);
        $this->expectExceptionMessage('Message formatter error using `some message with {invalid format}`');

        StringFormatter::format('en', 'some message with {invalid format}');
    }
}
