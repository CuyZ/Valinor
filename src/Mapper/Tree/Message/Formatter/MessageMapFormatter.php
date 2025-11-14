<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

use function is_string;

/**
 * Can be used to customize the content of messages added during a mapping.
 *
 * The constructor parameter is an array where each key represents either:
 * - The code of the message to be replaced
 * - The body of the message to be replaced
 * - The class name of the message to be replaced
 *
 * If none of those is found, the content of the message will stay unchanged
 * unless a default one is given to this class.
 *
 * If one of these keys is found, the array entry will be used to replace the
 * content of the message. This entry can be either a plain text or a callable
 * that takes the message as a parameter and returns a string; it is for
 * instance advised to use a callable in cases where a custom translation
 * service is used — to avoid useless greedy operations.
 *
 * See usage examples below:
 *
 * ```
 * $formatter = (new MessageMapFormatter([
 *     // Will match if the given message has this exact code
 *     'some_code' => 'New content / code: {message_code}',
 *
 *     // Will match if the given message has this exact content
 *     'Some message content' => 'New content / previous: {original_message}',
 *
 *     // Will match if the given message is an instance of `SomeError`
 *     SomeError::class => 'New content / value: {source_value}',
 *
 *     // A callback can be used to get access to the message instance
 *     OtherError::class => function (NodeMessage $message): string {
 *         if ($message->path() === 'foo.bar') {
 *             return 'Some custom message';
 *         }
 *
 *         return $message->body();
 *     },
 *
 *     // For greedy operation, it is advised to use a lazy-callback
 *     'foo' => fn () => $this->translator->translate('foo.bar'),
 * ]))
 *     ->defaultsTo('some default message')
 *     // …or…
 *     ->defaultsTo(fn () => $this->translator->translate('default_message'));
 *
 * $message = $formatter->format($message);
 * ```
 *
 * @api
 */
final class MessageMapFormatter implements MessageFormatter
{
    /** @var null|string|callable(NodeMessage): string */
    private $default;

    public function __construct(
        /** @var array<string|callable(NodeMessage): string> */
        private array $map
    ) {}

    /** @pure */
    public function format(NodeMessage $message): NodeMessage
    {
        $target = $this->target($message);

        if ($target) {
            return $message->withBody(is_string($target) ? $target : $target($message));
        }

        return $message;
    }

    /**
     * @param string|callable(NodeMessage): string $default
     */
    public function defaultsTo(string|callable $default): self
    {
        $clone = clone $this;
        $clone->default = $default;

        return $clone;
    }

    /**
     * @return false|string|callable(NodeMessage): string
     */
    private function target(NodeMessage $message): false|string|callable
    {
        return $this->map[$message->code()]
            ?? $this->map[$message->body()]
            ?? $this->map[$message->originalMessage()::class]
            ?? $this->default
            ?? false;
    }
}
