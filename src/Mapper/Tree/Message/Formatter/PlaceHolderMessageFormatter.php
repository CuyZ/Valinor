<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

/**
 * @api
 *
 * @deprecated This class is here to replace the old implementation of the
 * `NodeMessage::format` method; It should not be used anymore
 *
 * ---
 *
 * Performs a placeholders replace operation on the message body.
 *
 * The values to be replaced will be the ones given in the constructor; if none
 * is given these values will be used instead, in order:
 *
 * 1. The original code of this message
 * 2. The original content of this message
 * 3. A string representation of the node type
 * 4. The name of the node
 * 5. The path of the node
 */
final class PlaceHolderMessageFormatter implements MessageFormatter
{
    /** @var string[] */
    private array $values;

    public function __construct(string ...$values)
    {
        $this->values = $values;
    }

    public function format(NodeMessage $message): NodeMessage
    {
        $body = sprintf($message->body(), ...$this->values ?: [
            $message->code(),
            $message->withBody('{original_message}'),
            "`{$message->node()->type()}`",
            $message->node()->name(),
            $message->node()->path(),
        ]);

        return $message->withBody($body);
    }
}
