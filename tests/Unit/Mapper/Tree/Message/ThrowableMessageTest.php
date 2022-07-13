<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ThrowableMessageTest extends TestCase
{
    public function test_properties_can_be_accessed(): void
    {
        $message = 'some message';
        $code = 'some code';

        $codedError = ThrowableMessage::new($message, $code);

        self::assertSame($message, $codedError->body());
        self::assertSame($code, $codedError->code());
    }

    public function test_from_throwable_returns_error_message(): void
    {
        $original = new RuntimeException('some message', 1337);
        $message = ThrowableMessage::from($original);

        self::assertSame('some message', $message->getMessage());
        self::assertSame('1337', $message->code());
    }
}
