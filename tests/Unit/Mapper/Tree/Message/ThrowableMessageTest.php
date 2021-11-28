<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage;
use PHPUnit\Framework\TestCase;

final class ThrowableMessageTest extends TestCase
{
    public function test_properties_can_be_accessed(): void
    {
        $message = 'some message';
        $code = 'some code';

        $codedError = new ThrowableMessage($message, $code);

        self::assertSame($message, (string)$codedError);
        self::assertSame($code, $codedError->code());
    }
}
