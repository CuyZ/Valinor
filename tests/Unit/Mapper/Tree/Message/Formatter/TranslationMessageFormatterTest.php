<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\TranslationMessageFormatter;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeNodeMessage;
use PHPUnit\Framework\TestCase;

final class TranslationMessageFormatterTest extends TestCase
{
    public function test_format_message_formats_message_correctly(): void
    {
        $formatter = (new TranslationMessageFormatter())->withTranslations([
            'some key' => [
                'en' => 'some message',
            ],
        ]);

        $message = FakeNodeMessage::withMessage(new FakeMessage('some key'));
        $message = $formatter->format($message);

        self::assertSame('some message', (string)$message);
    }

    public function test_format_message_with_added_translation_formats_message_correctly(): void
    {
        $formatter = (new TranslationMessageFormatter())->withTranslations([
            'some key' => [
                'en' => 'some message',
            ],
        ])->withTranslation(
            'en',
            'some key',
            'some other message'
        );

        $message = FakeNodeMessage::withMessage(new FakeMessage('some key'));
        $message = $formatter->format($message);

        self::assertSame('some other message', (string)$message);
    }

    public function test_format_message_with_overridden_translations_formats_message_correctly(): void
    {
        $formatter = (new TranslationMessageFormatter())
            ->withTranslations([
                'some key' => [
                    'en' => 'some message',
                ],
            ])->withTranslations([
                'some key' => [
                    'en' => 'some other message',
                ],
            ]);

        $message = FakeNodeMessage::withMessage(new FakeMessage('some key'));
        $message = $formatter->format($message);

        self::assertSame('some other message', (string)$message);
    }

    public function test_format_message_with_overridden_translations_keeps_other_translations(): void
    {
        $formatter = (new TranslationMessageFormatter())
            ->withTranslations([
                'some key' => [
                    'en' => 'some message',
                ],
                'some other key' => [
                    'en' => 'some other message',
                ],
            ])->withTranslations([
                'some key' => [
                    'en' => 'some new message',
                ],
            ]);

        $message = FakeNodeMessage::withMessage(new FakeMessage('some other key'));
        $message = $formatter->format($message);

        self::assertSame('some other message', (string)$message);
    }

    public function test_format_message_with_default_translations_formats_message_correctly(): void
    {
        $formatter = TranslationMessageFormatter::default()->withTranslation(
            'en',
            'Value {value} is not accepted.',
            'Value {value} is not accepted!'
        );

        $originalMessage = (new FakeMessage('Value {value} is not accepted.'))->withParameters(['value' => 'foo']);
        $message = FakeNodeMessage::withMessage($originalMessage);
        $message = $formatter->format($message);

        self::assertSame('Value foo is not accepted!', (string)$message);
    }

    public function test_format_message_with_unknown_translation_returns_same_instance(): void
    {
        $messageA = FakeNodeMessage::any();
        $messageB = (new TranslationMessageFormatter())->format($messageA);

        self::assertSame($messageA, $messageB);
    }

    public function test_with_translation_returns_clone(): void
    {
        $formatterA = new TranslationMessageFormatter();
        $formatterB = $formatterA->withTranslation('en', 'some message', 'some other message');

        self::assertNotSame($formatterA, $formatterB);
    }

    public function test_with_translations_returns_clone(): void
    {
        $formatterA = new TranslationMessageFormatter();
        $formatterB = $formatterA->withTranslations([
            'some message' => [
                'en' => 'some other message',
            ],
        ]);

        self::assertNotSame($formatterA, $formatterB);
    }
}
