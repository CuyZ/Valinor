<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\DefaultMessage;
use PHPUnit\Framework\TestCase;

final class DefaultMessageTest extends TestCase
{
    public function test_english_translations_match_messages(): void
    {
        foreach (DefaultMessage::TRANSLATIONS as $message => $translations) {
            self::assertSame(
                $message,
                $translations['en'],
                'The english translation of the message body should be the same as the body itself.',
            );
        }
    }
}
