<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Type\Type;
use Throwable;

use function sprintf;

/** @api */
final class NodeMessage implements Message, HasCode
{
    private Node $node;

    private Message $message;

    public function __construct(Node $node, Message $message)
    {
        $this->node = $node;
        $this->message = $message;
    }

    /**
     * Performs a placeholders replace operation on the given content.
     *
     * The values to be replaced will be the ones given as second argument; if
     * none is given these values will be used instead, in order:
     *
     * 1. The original code of this message
     * 2. The original content of this message
     * 3. A string representation of the node type
     * 4. The name of the node
     * 5. The path of the node
     *
     * See usage examples below:
     *
     * ```php
     * $content = $message->format('the previous code was: %1$s');
     *
     * $content = $message->format(
     *     '%1$s / new message content (type: %2$s)',
     *     'some parameter',
     *     $message->type(),
     * );
     * ```
     */
    public function format(string $content, string ...$values): string
    {
        return sprintf($content, ...$values ?: [
            $this->code(),
            (string)$this,
            (string)$this->type(),
            $this->name(),
            $this->path(),
        ]);
    }

    public function name(): string
    {
        return $this->node->name();
    }

    public function path(): string
    {
        return $this->node->path();
    }

    public function type(): Type
    {
        return $this->node->type();
    }

    public function attributes(): Attributes
    {
        return $this->node->attributes();
    }

    /**
     * @return mixed
     */
    public function value()
    {
        if (! $this->node->isValid()) {
            return null;
        }

        return $this->node->value();
    }

    public function originalMessage(): Message
    {
        return $this->message;
    }

    public function isError(): bool
    {
        return $this->message instanceof Throwable;
    }

    public function code(): string
    {
        if ($this->message instanceof HasCode) {
            return $this->message->code();
        }

        if ($this->message instanceof Throwable) {
            return (string)$this->message->getCode();
        }

        return 'unknown';
    }

    public function __toString(): string
    {
        if ($this->message instanceof Throwable) {
            return $this->message->getMessage();
        }

        return (string)$this->message;
    }
}
