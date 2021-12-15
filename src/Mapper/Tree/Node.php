<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotGetInvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Exception\DuplicatedNodeChild;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Type;
use Throwable;

final class Node
{
    private Shell $shell;

    /** @var mixed */
    private $value;

    /** @var array<Node> */
    private array $children = [];

    /** @var array<Message> */
    private array $messages = [];

    private bool $valid = true;

    /**
     * @param mixed $value
     */
    private function __construct(Shell $shell, $value)
    {
        $this->shell = $shell;
        $this->value = $value;
    }

    /**
     * @param mixed $value
     */
    public static function leaf(Shell $shell, $value): self
    {
        $instance = new self($shell, $value);
        $instance->check();

        return $instance;
    }

    /**
     * @param mixed $value
     * @param array<Node> $children
     */
    public static function branch(Shell $shell, $value, array $children): self
    {
        $instance = new self($shell, $value);

        foreach ($children as $child) {
            $name = $child->name();

            if (isset($instance->children[$name])) {
                throw new DuplicatedNodeChild($name);
            }

            $instance->children[$name] = $child;
        }

        $instance->check();

        return $instance;
    }

    /**
     * @param Throwable&Message $message
     */
    public static function error(Shell $shell, Throwable $message): self
    {
        return (new self($shell, null))->withMessage($message);
    }

    public function name(): string
    {
        return $this->shell->name();
    }

    public function isRoot(): bool
    {
        return $this->shell->isRoot();
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
     * @param mixed $value
     */
    public function withValue($value): self
    {
        $clone = clone $this;
        $clone->value = $value;
        $clone->check();

        return $clone;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        if (! $this->valid) {
            throw new CannotGetInvalidNodeValue($this);
        }

        return $this->value;
    }

    /**
     * @return array<Node>
     */
    public function children(): array
    {
        return $this->children;
    }

    public function withMessage(Message $message): self
    {
        $clone = clone $this;
        $clone->messages = [...$this->messages, $message];

        if ($message instanceof Throwable) {
            $clone->valid = false;
        }

        return $clone;
    }

    /**
     * @return array<Message>
     */
    public function messages(): array
    {
        return $this->messages;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    private function check(): void
    {
        foreach ($this->children as $child) {
            if (! $child->valid) {
                $this->valid = false;

                return;
            }
        }

        if ($this->valid && ! $this->shell->type()->accepts($this->value)) {
            throw new InvalidNodeValue($this->value, $this->shell->type());
        }
    }
}
