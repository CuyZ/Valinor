<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\ValueDumper;
use Throwable;

/** @api */
final class NodeMessage implements Message, HasCode
{
    private Node $node;

    private Message $message;

    private string $body;

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
     * @deprecated use `$message->node()->name()` instead
     */
    public function name(): string
    {
        return $this->node->name();
    }

    /**
     * @deprecated use `$message->node()->path()` instead
     */
    public function path(): string
    {
        return $this->node->path();
    }

    /**
     * @deprecated use `$message->node()->type()` instead
     */
    public function type(): string
    {
        return $this->node->type();
    }

    /**
     * @deprecated use `$message->node()->mappedValue()` instead
     *
     * @return mixed
     */
    public function value()
    {
        return $this->node->mappedValue();
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
            'source_value' => $sourceValue = $this->node->sourceFilled() ? ValueDumper::dump($this->node->sourceValue()) : '*missing*',
            'original_value' => $sourceValue, // @deprecated
        ];

        if ($this->message instanceof HasParameters) {
            $parameters += $this->message->parameters();
        }

        $parameters['original_message'] = $this->format($this->message->body(), $parameters);

        return $parameters;
    }
}
