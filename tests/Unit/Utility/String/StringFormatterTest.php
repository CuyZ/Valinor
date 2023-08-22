<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\String;

use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\String\StringFormatterError;
use PHPUnit\Framework\TestCase;

final class StringFormatterTest extends TestCase
{
    /**
     * @requires extension intl
     */
    public function test_wrong_intl_format_throws_exception(): void
    {
        $this->expectException(StringFormatterError::class);
        $this->expectExceptionMessage('Message formatter error using `some {wrong.format}`');
        $this->expectExceptionCode(1652901203);

        StringFormatter::format('en', 'some {wrong.format}', []);
    }

    /**
     * @requires extension intl
     */
    public function test_wrong_intl_format_throws_exception_with_intl_exception(): void
    {
        $oldIni = ini_get('intl.use_exceptions');
        try {
            ini_set('intl.use_exceptions', '1');
            $this->expectException(StringFormatterError::class);
            $this->expectExceptionMessage('Message formatter error using `some {wrong.format}`');
            $this->expectExceptionCode(1652901203);

            StringFormatter::format('en', 'some {wrong.format}', []);
        } finally {
            ini_set('intl.use_exceptions', $oldIni);
        }
    }

    public function test_wrong_message_body_format_throws_exception(): void
    {
        $this->expectException(StringFormatterError::class);
        $this->expectExceptionMessage('Message formatter error using `some message with {invalid format}`');
        $this->expectExceptionCode(1652901203);

        StringFormatter::format('en', 'some message with {invalid format}');
    }

    public function test_parameters_are_replaced_correctly(): void
    {
        $message = (new FakeMessage('some message with {valid_parameter}'))->withParameters([
            'invalid )( parameter' => 'invalid value',
            'valid_parameter' => 'valid value',
        ]);

        self::assertSame('some message with valid value', StringFormatter::for($message));
    }
}
