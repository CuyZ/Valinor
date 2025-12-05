<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use RuntimeException;
use Throwable;

/**
 * Can be used to easily create an instance of (error) message.
 *
 * ```
 * $message = MessageBuilder::newError('Some message with {some_parameter}.')
 *     ->withCode('some_code')
 *     ->withParameter('some_parameter', 'some_value')
 *     ->build();
 * ```
 *
 * @api
 *
 * @template MessageType of Message
 */
final class MessageBuilder
{
    private bool $isError = false;

    private string $code = 'unknown';

    /** @var array<string, string> */
    private array $parameters = [];

    private function __construct(private string $body) {}

    /**
     * @pure
     * @return self<Message>
     */
    public static function new(string $body): self
    {
        return new self($body);
    }

    /**
     * @pure
     * @return self<ErrorMessage&Throwable>
     */
    public static function newError(string $body): self
    {
        /** @var self<ErrorMessage&Throwable> $instance */
        $instance = new self($body);
        $instance->isError = true;

        return $instance;
    }

    /** @pure */
    public static function from(Throwable $error): ErrorMessage
    {
        if ($error instanceof ErrorMessage) {
            return $error;
        }

        return self::newError($error->getMessage())
            ->withCode($error->getCode() === 0 ? 'unknown' : (string)$error->getCode())
            ->build();
    }

    /**
     * @pure
     * @return self<MessageType>
     */
    public function withBody(string $body): self
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    /** @pure */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * @pure
     * @return self<MessageType>
     */
    public function withCode(string $code): self
    {
        $clone = clone $this;
        $clone->code = $code;

        return $clone;
    }

    /** @pure */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * @pure
     * @return self<MessageType>
     */
    public function withParameter(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->parameters[$name] = $value;

        return $clone;
    }

    /**
     * @pure
     * @return array<string, string>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @pure
     * @return MessageType&HasCode&HasParameters
     */
    public function build(): Message&HasCode&HasParameters
    {
        /** @var MessageType&HasCode&HasParameters */
        return $this->isError
            ? $this->buildErrorMessage()
            : $this->buildMessage();
    }

    private function buildMessage(): Message&HasCode&HasParameters
    {
        return new class ($this->body, $this->code, $this->parameters) implements Message, HasCode, HasParameters {
            /**
             * @param array<string, string> $parameters
             */
            public function __construct(
                private string $body,
                private string $code,
                private array  $parameters
            ) {}

            public function body(): string
            {
                return $this->body;
            }

            public function code(): string
            {
                return $this->code;
            }

            public function parameters(): array
            {
                return $this->parameters;
            }
        };
    }

    private function buildErrorMessage(): ErrorMessage&Throwable&HasCode&HasParameters
    {
        return new class ($this->body, $this->code, $this->parameters) extends RuntimeException implements ErrorMessage, HasCode, HasParameters {
            /**
             * @param array<string, string> $parameters
             */
            public function __construct(string $body, string $code, private array $parameters)
            {
                parent::__construct($body);

                $this->code = $code;
            }

            public function body(): string
            {
                /** @var string */
                return $this->message;
            }

            public function code(): string
            {
                /** @var string */
                return $this->code;
            }

            public function parameters(): array
            {
                return $this->parameters;
            }
        };
    }
}
