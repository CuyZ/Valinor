<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
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

        self::assertSame($message, (string)$codedError);
        self::assertSame($code, $codedError->code());
    }

    public function test_from_message_returns_message(): void
    {
        $message = new class ('some message') extends RuntimeException implements Message { };

        self::assertSame($message, ThrowableMessage::from($message));
    }

    public function test_from_throwable_returns_message(): void
    {
        $message = new RuntimeException('some message', 1337);
        $throwableMessage = ThrowableMessage::from($message);

        self::assertSame('some message', (string)$throwableMessage);
        self::assertSame('1337', $throwableMessage->code()); // @phpstan-ignore-line
    }
}
