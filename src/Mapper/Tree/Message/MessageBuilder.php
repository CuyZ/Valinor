<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use LanguageServerProtocol\MessageType;
use RuntimeException;
use Throwable;

/**
 * Can be used to easily create an instance of (error) message.
 *
 * ```php
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

    private function __construct(private string $body)
    {
    }

    /**
     * @return self<Message>
     */
    public static function new(string $body): self
    {
        return new self($body);
    }

    /**
     * @return self<ErrorMessage>
     */
    public static function newError(string $body): self
    {
        $instance = new self($body);
        $instance->isError = true;

        /** @var self<ErrorMessage> */
        return $instance;
    }

    public static function from(Throwable $error): ErrorMessage
    {
        if ($error instanceof ErrorMessage) {
            return $error;
        }

        return self::newError($error->getMessage())
            ->withCode((string)$error->getCode())
            ->build();
    }

    /**
     * @return self<MessageType>
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
     * @return self<MessageType>
     */
    public function withCode(string $code): self
    {
        $clone = clone $this;
        $clone->code = $code;

        return $clone;
    }

    public function code(): string
    {
        return $this->code;
    }

    /**
     * @return self<MessageType>
     */
    public function withParameter(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->parameters[$name] = $value;

        return $clone;
    }

    /**
     * @return array<string, string>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * PHP8.1 intersection
     * @return MessageType&HasCode&HasParameters
     */
    public function build(): Message
    {
        /** @var MessageType&HasCode&HasParameters */
        return $this->isError
            ? $this->buildErrorMessage()
            : $this->buildMessage();
    }

    private function buildMessage(): Message
    {
        return new class ($this->body, $this->code, $this->parameters) implements Message, HasCode, HasParameters {
            /**
             * @param array<string, string> $parameters
             */
            public function __construct(private string $body, private string $code, private array $parameters)
            {
            }

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

    private function buildErrorMessage(): ErrorMessage
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
                return $this->message;
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
}
