<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\PlaceHolderMessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeShell;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\Formatter\FakeMessageFormatter;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class PlaceHolderMessageFormatterTest extends TestCase
{
    public function test_format_message_replaces_placeholders_with_default_values(): void
    {
        $type = FakeType::permissive();
        $shell = FakeShell::any()->child('foo', $type, 'some value');

        $message = new NodeMessage($shell, new FakeMessage('some message'));
        $message = (new FakeMessageFormatter('%1$s / %2$s / %3$s / %4$s / %5$s'))->format($message);
        $message = (new PlaceHolderMessageFormatter())->format($message);

        self::assertSame("some_code / some message / `$type` / foo / foo", (string)$message);
    }

    public function test_format_message_replaces_correct_original_value_if_throwable(): void
    {
        $message = new NodeMessage(FakeShell::any(), new FakeErrorMessage('some error message'));
        $message = (new FakeMessageFormatter('original: %2$s'))->format($message);
        $message = (new PlaceHolderMessageFormatter())->format($message);

        self::assertSame('original: some error message', (string)$message);
    }

    public function test_format_message_replaces_placeholders_with_given_values(): void
    {
        $formatter = new PlaceHolderMessageFormatter('foo', 'bar');

        $message = new NodeMessage(FakeShell::any(), new FakeMessage('%1$s / %2$s'));
        $message = $formatter->format($message);

        self::assertSame('foo / bar', (string)$message);
    }
}
