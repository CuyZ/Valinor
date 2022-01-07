<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\EmptyAttributes;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotGetParentOfRootShell;
use CuyZ\Valinor\Mapper\Tree\Exception\NewShellTypeDoesNotMatch;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function array_unshift;
use function implode;

/** @api */
final class Shell
{
    private string $name;

    private Type $type;

    /** @var mixed */
    private $value;

    private Attributes $attributes;

    private self $parent;

    /**
     * @param mixed $value
     */
    private function __construct(Type $type, $value)
    {
        $this->type = $type;
        $this->value = $value;

        if ($type instanceof UnresolvableType) {
            throw new UnresolvableShellType($type);
        }
    }

    /**
     * @param mixed $value
     */
    public static function root(Type $type, $value): self
    {
        return new self($type, $value);
    }

    /**
     * @param mixed $value
     */
    public function child(string $name, Type $type, $value, Attributes $attributes = null): self
    {
        $instance = new self($type, $value);
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

        if (! $this->type->matches($newType)) {
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
        $clone->value = $value;

        return $clone;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    public function attributes(): Attributes
    {
        return $this->attributes ?? EmptyAttributes::get();
    }

    public function path(): string
    {
        $node = $this;
        $path = [];

        while (isset($node->parent)) {
            array_unshift($path, $node->name);
            $node = $node->parent;
        }

        return implode('.', $path);
    }
}
