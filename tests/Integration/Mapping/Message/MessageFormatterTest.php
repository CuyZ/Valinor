<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Message;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Formatter\AggregateMessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\Formatter\LocaleMessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\Formatter\TranslationMessageFormatter;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class MessageFormatterTest extends IntegrationTest
{
    public function test_message_is_formatted_correctly(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('int', 'foo');
        } catch (MappingError $error) {
            $formatter = new AggregateMessageFormatter(
                new LocaleMessageFormatter('fr'),
                new MessageMapFormatter([
                    'Cannot cast {value} to {expected_type}.' => 'New message: {value} / {expected_type}',
                ]),
                (new TranslationMessageFormatter())->withTranslation(
                    'fr',
                    'New message: {value} / {expected_type}',
                    'Nouveau message : {value} / {expected_type}',
                ),
            );

            $message = $formatter->format($error->node()->messages()[0]);

            self::assertSame("Nouveau message : 'foo' / `int`", (string)$message);
        }
    }
}
