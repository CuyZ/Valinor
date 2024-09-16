<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedKeysInSource;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Type;
use Throwable;

use function array_diff;
use function array_keys;
use function array_map;
use function assert;
use function is_array;

/** @internal */
final class TreeNode
{
    private Shell $shell;

    private mixed $value;

    /** @var array<self> */
    private array $children = [];

    /** @var array<Message> */
    private array $messages = [];

    private bool $valid = true;

    private function __construct(Shell $shell, mixed $value)
    {
        $this->shell = $shell;
        $this->value = $value;
    }

    public static function leaf(Shell $shell, mixed $value): self
    {
        $instance = new self($shell, $value);
        $instance->check();

        return $instance;
    }

    /**
     * @param array<self> $children
     */
    public static function branch(Shell $shell, mixed $value, array $children): self
    {
        $instance = new self($shell, $value);

        foreach ($children as $child) {
            $instance->children[$child->name()] = $child;
        }

        $instance->check();

        return $instance;
    }

    public static function flattenedBranch(Shell $shell, mixed $value, self $child): self
    {
        $instance = new self($shell, $value);
        $instance->messages = $child->messages;
        $instance->children = $child->children;
        $instance->valid = $child->valid;

        return $instance;
    }

    public static function error(Shell $shell, Throwable&Message $message): self
    {
        return (new self($shell, null))->withMessage($message);
    }

    public function name(): string
    {
        return $this->shell->name();
    }

    public function type(): Type
    {
        return $this->shell->type();
    }

    /**
     * @return array<self>
     */
    public function children(): array
    {
        return $this->children;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function withValue(mixed $value): self
    {
        $clone = clone $this;
        $clone->value = $value;
        $clone->check();

        return $clone;
    }

    public function value(): mixed
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

    public function checkUnexpectedKeys(): self
    {
        $value = $this->shell->value();

        if ($this->shell->allowSuperfluousKeys() || ! is_array($value)) {
            return $this;
        }

        $diff = array_diff(array_keys($value), array_keys($this->children), $this->shell->allowedSuperfluousKeys());

        if ($diff !== []) {
            return $this->withMessage(new UnexpectedKeysInSource($value, $this->children));
        }

        return $this;
    }

    private function check(): void
    {
        foreach ($this->children as $child) {
            if (! $child->valid) {
                $this->valid = false;
            }
        }

        $type = $this->shell->type();

        if ($this->valid && ! $type->accepts($this->value)) {
            $this->valid = false;
            $this->messages[] = new InvalidNodeValue($type);
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
