<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\AggregateMessageFormatter;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeNodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\Formatter\FakeMessageFormatter;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;

final class AggregateMessageFormatterTest extends UnitTestCase
{
    public function test_formatters_are_called_in_correct_order(): void
    {
        $formatter = new AggregateMessageFormatter(
            FakeMessageFormatter::withPrefix('prefix A:'),
            FakeMessageFormatter::withPrefix('prefix B:'),
        );

        $message = $formatter->format(FakeNodeMessage::new());

        self::assertSame('prefix B: prefix A: some message', (string)$message);
    }
}
