<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

/**
 * Can be used to easily customize a message with the given callback.
 *
 * Example:
 *
 * ```
 * // Customize the body of messages that have a certain code.
 * $formatter = new CallbackMessageFormatter(
 *     fn (NodeMessage $message) => match ($message->code()) {
 *         'some_code_a',
 *         'some_code_b',
 *         'some_code_c' => $message->withBody('some new message body'),
 *         default => $message
 *     }
 * );
 *
 * $message = $formatter->format($message);
 * ```
 *
 * @api
 */
final class CallbackMessageFormatter implements MessageFormatter
{
    /** @var callable(NodeMessage): NodeMessage */
    private $callback;

    /**
     * @param callable(NodeMessage): NodeMessage $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /** @pure */
    public function format(NodeMessage $message): NodeMessage
    {
        return ($this->callback)($message);
    }
}
