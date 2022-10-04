<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\EmptyAttributes;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotGetParentOfRootShell;
use CuyZ\Valinor\Mapper\Tree\Exception\NewShellTypeDoesNotMatch;
use CuyZ\Valinor\Mapper\Tree\Exception\ShellHasNoValue;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function array_unshift;
use function implode;

/** @internal */
final class Shell
{
    private string $name;

    private Type $type;

    private bool $hasValue = false;

    /** @var mixed */
    private $value;

    private Attributes $attributes;

    private self $parent;

    private function __construct(Type $type)
    {
        $this->type = $type;

        if ($type instanceof UnresolvableType) {
            throw new UnresolvableShellType($type);
        }
    }

    /**
     * @param mixed $value
     */
    public static function root(Type $type, $value): self
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

    public function parent(): self
    {
        if (! isset($this->parent)) {
            throw new CannotGetParentOfRootShell();
        }

        return $this->parent;
    }

    public function withType(Type $newType): self
    {
        $clone = clone $this;
        $clone->type = $newType;

        if (! $newType->matches($this->type)) {
            throw new NewShellTypeDoesNotMatch($this, $newType);
        }

        return $clone;
    }

    public function type(): Type
    {
        return $this->type;
    }

    /**
     * @param mixed $value
     */
    public function withValue($value): self
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

    /**
     * @return mixed
     */
    public function value()
    {
        if (! $this->hasValue) {
            throw new ShellHasNoValue();
        }

        return $this->value;
    }

    public function attributes(): Attributes
    {
        return $this->attributes ?? EmptyAttributes::get();
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
