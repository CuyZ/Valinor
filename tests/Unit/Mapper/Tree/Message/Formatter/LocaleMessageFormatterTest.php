<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\LocaleMessageFormatter;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeNodeMessage;
use PHPUnit\Framework\TestCase;

final class LocaleMessageFormatterTest extends TestCase
{
    public function test_locale_is_updated_for_message(): void
    {
        $message = FakeNodeMessage::any();
        $message = (new LocaleMessageFormatter('fr'))->format($message);

        self::assertSame('fr', $message->locale());
    }
}
