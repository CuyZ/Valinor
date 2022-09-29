<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\DuplicatedNodeChild;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use Throwable;

use function assert;

/** @internal */
final class TreeNode
{
    private Shell $shell;

    /** @var mixed */
    private $value;

    /** @var array<self> */
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
     * @param array<self> $children
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

    public function isValid(): bool
    {
        return $this->valid;
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
        assert($this->valid, "Trying to get value of an invalid node at path `{$this->shell->path()}`.");

        return $this->value;
    }

    public function withMessage(Message $message): self
    {
        $clone = clone $this;
        $clone->messages[] = $message;
        $clone->valid = $clone->valid && ! $message instanceof Throwable;

        return $clone;
    }

    public function node(): Node
    {
        return $this->buildNode($this);
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
            $this->valid = false;
            $this->messages[] = new InvalidNodeValue($this->shell->type());
        }
    }

    private function buildNode(self $self): Node
    {
        return new Node(
            $self->shell->isRoot(),
            $self->shell->name(),
            $self->shell->path(),
            $self->shell->type()->toString(),
            $self->shell->hasValue(),
            $self->shell->hasValue() ? $self->shell->value() : null,
            $self->valid ? $self->value : null,
            $self->messages,
            array_map(
                fn (self $child) => $self->buildNode($child),
                $self->children
            )
        );
    }
}
