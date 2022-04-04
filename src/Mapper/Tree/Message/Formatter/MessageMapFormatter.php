<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

use function get_class;
use function is_callable;

/**
 * Can be used to customize the content of messages added during a mapping.
 *
 * The constructor parameter is an array where each key represents either:
 * - The code of the message to be replaced
 * - The content of the message to be replaced
 * - The class name of the message to be replaced
 *
 * If none of those is found, the content of the message will stay unchanged
 * unless a default one is given to this class.
 *
 * If one of these keys is found, the array entry will be used to replace the
 * content of the message. This entry can be either a plain text or a callable
 * that takes the message as a parameter and returns a string; it is for
 * instance advised to use a callable in cases where a translation service is
 * used — to avoid useless greedy operations.
 *
 * In any case, the content can contain placeholders that can be used the same
 * way as @see \CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::format()
 *
 * See usage examples below:
 *
 * ```php
 * $formatter = (new MessageMapFormatter([
 *     // Will match if the given message has this exact code
 *     'some_code' => 'new content / previous code was: %1$s',
 *
 *     // Will match if the given message has this exact content
 *     'Some message content' => 'new content / previous message: %2$s',
 *
 *     // Will match if the given message is an instance of `SomeError`
 *     SomeError::class => '
 *         - Original code of the message: %1$s
 *         - Original content of the message: %2$s
 *         - Node type: %3$s
 *         - Node name: %4$s
 *         - Node path: %5$s
 *     ',
 *
 *     // A callback can be used to get access to the message instance
 *     OtherError::class => function (NodeMessage $message): string {
 *         if ((string)$message->type() === 'string|int') {
 *             // …
 *         }
 *
 *         return 'Some message content';
 *     },
 *
 *     // For greedy operation, it is advised to use a lazy-callback
 *     'foo' => fn () => $this->translator->translate('foo.bar'),
 * ]))
 *     ->defaultsTo('some default message')
 *     // …or…
 *     ->defaultsTo(fn () => $this->translator->translate('default_message'));
 *
 * $content = $formatter->format($message);
 * ```
 *
 * @api
 */
final class MessageMapFormatter implements MessageFormatter
{
    /** @var array<string|callable(NodeMessage): string> */
    private array $map;

    /** @var null|string|callable(NodeMessage): string */
    private $default;

    /**
     * @param array<string|callable(NodeMessage): string> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function format(NodeMessage $message): string
    {
        $target = $this->target($message);
        $text = is_callable($target) ? $target($message) : $target;

        return $message->format($text);
    }

    /**
     * @param string|callable(NodeMessage): string $default
     */
    public function defaultsTo($default): self
    {
        $clone = clone $this;
        $clone->default = $default;

        return $clone;
    }

    /**
     * @return string|callable(NodeMessage): string
     */
    private function target(NodeMessage $message)
    {
        return $this->map[$message->code()]
            ?? $this->map[(string)$message]
            ?? $this->map[get_class($message->originalMessage())]
            ?? $this->default
            ?? (string)$message;
    }
}
