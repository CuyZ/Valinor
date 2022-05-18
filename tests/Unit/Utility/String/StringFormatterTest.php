<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\String;

use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeTranslatableMessage;
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
        $this->expectExceptionMessage('Message formatter error using `some {wrong.format}`.');
        $this->expectExceptionCode(1652901203);

        StringFormatter::format('en', 'some {wrong.format}', []);
    }

    public function test_wrong_message_body_format_throws_exception(): void
    {
        $this->expectException(StringFormatterError::class);
        $this->expectExceptionMessage('Message formatter error using `some message with {invalid format}`.');
        $this->expectExceptionCode(1652901203);

        StringFormatter::for(new FakeTranslatableMessage('some message with {invalid format}'));
    }

    public function test_parameters_are_replaced_correctly(): void
    {
        $message = new FakeTranslatableMessage('some message with {valid_parameter}', [
            'invalid )( parameter' => 'invalid value',
            'valid_parameter' => 'valid value',
        ]);

        self::assertSame('some message with valid value', StringFormatter::for($message));
    }
}
