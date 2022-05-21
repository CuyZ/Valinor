<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\AggregateMessageFormatter;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeNodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\Formatter\FakeMessageFormatter;
use PHPUnit\Framework\TestCase;

final class AggregateMessageFormatterTest extends TestCase
{
    public function test_formatters_are_called_in_correct_order(): void
    {
        $formatter = new AggregateMessageFormatter(
            new FakeMessageFormatter('message A'),
            new FakeMessageFormatter('message B'),
        );

        $message = $formatter->format(FakeNodeMessage::any());

        self::assertSame('message B', (string)$message);
    }
}
