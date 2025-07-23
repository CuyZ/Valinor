<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use CuyZ\Valinor\Utility\String\StringFormatter;
use Stringable;
use Throwable;

use function array_merge;

/** @api */
final class NodeMessage implements Message, HasCode, Stringable
{
    /** @var array<string, string> */
    private array $parameters = [];

    private string $locale = StringFormatter::DEFAULT_LOCALE;

    /**
     * @internal
     */
    public function __construct(
        private Message $message,
        private string $body,
        private string $name,
        private string $path,
        private string $type,
        private string $sourceValue,
    ) {}

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

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function sourceValue(): string
    {
        return $this->sourceValue;
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
        return $this->message instanceof ErrorMessage;
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
            'node_name' => $this->name,
            'node_path' => $this->path,
            'node_type' => $this->type,
            'source_value' => $this->sourceValue,
        ];

        if ($this->message instanceof HasParameters) {
            $parameters += $this->message->parameters();
        }

        $parameters['original_message'] = $this->format($this->message->body(), $parameters);

        return array_merge($parameters, $this->parameters);
    }
}
