<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use CuyZ\Valinor\Utility\ValueDumper;
use Throwable;

/** @api */
final class NodeMessage implements Message, HasCode
{
    private Shell $shell;

    private Message $message;

    private string $body;

    private string $locale = StringFormatter::DEFAULT_LOCALE;

    public function __construct(Shell $shell, Message $message)
    {
        $this->shell = $shell;
        $this->message = $message;

        if ($this->message instanceof TranslatableMessage) {
            $this->body = $this->message->body();
        } elseif ($this->message instanceof Throwable) {
            $this->body = $this->message->getMessage();
        } else {
            $this->body = (string)$this->message;
        }
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

    public function name(): string
    {
        return $this->shell->name();
    }

    public function path(): string
    {
        return $this->shell->path();
    }

    public function type(): Type
    {
        return $this->shell->type();
    }

    public function attributes(): Attributes
    {
        return $this->shell->attributes();
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->shell->value();
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
        return StringFormatter::format($this->locale, $this->body, $this->parameters());
    }

    /**
     * @return array<string, string>
     */
    private function parameters(): array
    {
        $parameters = [
            'message_code' => $this->code(),
            'node_name' => $this->shell->name(),
            'node_path' => $this->shell->path(),
            'node_type' => TypeHelper::dump($this->shell->type()),
            'original_value' => ValueDumper::dump($this->shell->hasValue() ? $this->shell->value() : '*missing*'),
            'original_message' => $this->message instanceof Throwable ? $this->message->getMessage() : $this->message->__toString(),
        ];

        if ($this->message instanceof TranslatableMessage) {
            $parameters += $this->message->parameters();
        }

        return $parameters;
    }
}
