<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function array_unshift;
use function assert;
use function implode;

/** @internal */
final class Shell
{
    private string $name;

    private bool $hasValue = false;

    private mixed $value = null;

    private Attributes $attributes;

    private self $parent;

    private function __construct(private Type $type)
    {
        if ($type instanceof UnresolvableType) {
            throw new UnresolvableShellType($type);
        }
    }

    public static function root(Type $type, mixed $value): self
    {
        return (new self($type))->withValue($value);
    }

    public function child(string $name, Type $type, Attributes $attributes = null): self
    {
        $instance = new self($type);
        $instance->name = $name;
        $instance->parent = $this;

        if ($attributes) {
            $instance->attributes = $attributes;
        }

        return $instance;
    }

    public function name(): string
    {
        return $this->name ?? '';
    }

    public function isRoot(): bool
    {
        return ! isset($this->parent);
    }

    public function withType(Type $newType): self
    {
        $clone = clone $this;
        $clone->type = $newType;

        return $clone;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function withValue(mixed $value): self
    {
        $clone = clone $this;
        $clone->hasValue = true;
        $clone->value = $value;

        return $clone;
    }

    public function hasValue(): bool
    {
        return $this->hasValue;
    }

    public function value(): mixed
    {
        assert($this->hasValue);

        return $this->value;
    }

    public function attributes(): Attributes
    {
        return $this->attributes ?? Attributes::empty();
    }

    public function path(): string
    {
        if (! isset($this->parent)) {
            return '*root*';
        }

        $node = $this;
        $path = [];

        while (isset($node->parent)) {
            array_unshift($path, $node->name);
            $node = $node->parent;
        }

        return implode('.', $path);
    }
}
