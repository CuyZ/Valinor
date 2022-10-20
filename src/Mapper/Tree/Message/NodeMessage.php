<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\ValueDumper;
use Stringable;
use Throwable;

use function array_merge;

/** @api */
final class NodeMessage implements Message, HasCode, Stringable
{
    private Node $node;

    private Message $message;

    private string $body;

    /** @var array<string, string> */
    private array $parameters = [];

    private string $locale = StringFormatter::DEFAULT_LOCALE;

    public function __construct(Node $node, Message $message)
    {
        $this->node = $node;
        $this->message = $message;
        $this->body = $message->body();
    }

    public function node(): Node
    {
        return $this->node;
    }

    public function withLocale(string $locale): self
    {
        $clone = clone $this;
        $clone->locale = $locale;

        return $clone;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * Allows to customize the body of the message. It can contain placeholders
     * that will be replaced by their corresponding values, as described below:
     *
     * | Placeholder          | Description                                    |
     * |----------------------|------------------------------------------------|
     * | `{message_code}`     | The code of the message                        |
     * | `{node_name}`        | Name of the node to which the message is bound |
     * | `{node_path}`        | Path of the node to which the message is bound |
     * | `{node_type}`        | Type of the node to which the message is bound |
     * | `{source_value}`     | The source value that was given to the node    |
     * | `{original_message}` | The original message before being customized   |
     *
     * Example:
     *
     * ```php
     * $message = $message->withBody('new message for value: {source_value}');
     * ```
     */
    public function withBody(string $body): self
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * Adds a parameter that can replace a placeholder in the message body.
     *
     * @see self::withBody()
     */
    public function withParameter(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->parameters[$name] = $value;

        return $clone;
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

    public function toString(): string
    {
        return $this->format($this->body, $this->parameters());
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param array<string, string> $parameters
     */
    private function format(string $body, array $parameters): string
    {
        return StringFormatter::format($this->locale, $body, $parameters);
    }

    /**
     * @return array<string, string>
     */
    private function parameters(): array
    {
        $parameters = [
            'message_code' => $this->code(),
            'node_name' => $this->node->name(),
            'node_path' => $this->node->path(),
            'node_type' => "`{$this->node->type()}`",
            'source_value' => $this->node->sourceFilled() ? ValueDumper::dump($this->node->sourceValue()) : '*missing*',
        ];

        if ($this->message instanceof HasParameters) {
            $parameters += $this->message->parameters();
        }

        $parameters['original_message'] = $this->format($this->message->body(), $parameters);

        return array_merge($parameters, $this->parameters);
    }
}
