<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use CuyZ\Valinor\Type\FloatType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function array_unshift;
use function assert;
use function implode;

/** @internal */
final class Shell
{
    private Settings $settings;

    private Type $type;

    private string $name;

    private bool $hasValue = false;

    private mixed $value = null;

    private Attributes $attributes;

    private self $parent;

    /** @var list<string> */
    private array $allowedSuperfluousKeys = [];

    private function __construct(Settings $settings, Type $type)
    {
        if ($type instanceof UnresolvableType) {
            throw new UnresolvableShellType($type);
        }

        $this->settings = $settings;
        $this->type = $type;
    }

    public static function root(
        Settings $settings,
        Type $type,
        mixed $value,
    ): self {
        return (new self($settings, $type))->withValue($value);
    }

    public function child(string $name, Type $type, ?Attributes $attributes = null): self
    {
        $instance = new self($this->settings, $type);
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
        $clone->value = self::castCompatibleValue($newType, $this->value);

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
        $clone->value = self::castCompatibleValue($clone->type, $value);

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

    public function enableFlexibleCasting(): bool
    {
        return $this->settings->enableFlexibleCasting;
    }

    public function allowSuperfluousKeys(): bool
    {
        return $this->settings->allowSuperfluousKeys;
    }

    public function allowPermissiveTypes(): bool
    {
        return $this->settings->allowPermissiveTypes;
    }

    public function attributes(): Attributes
    {
        return $this->attributes ?? Attributes::empty();
    }

    /**
     * @param list<string> $allowedSuperfluousKeys
     */
    public function withAllowedSuperfluousKeys(array $allowedSuperfluousKeys): self
    {
        $clone = clone $this;
        $clone->allowedSuperfluousKeys = $allowedSuperfluousKeys;

        return $clone;
    }

    /**
     * @return list<string>
     */
    public function allowedSuperfluousKeys(): array
    {
        return $this->allowedSuperfluousKeys;
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

    private static function castCompatibleValue(Type $type, mixed $value): mixed
    {
        // When the value is an integer and the type is a float, the value is
        // cast to float, to follow the rule of PHP regarding acceptance of an
        // integer value in a float type. Note that PHPStan/Psalm analysis
        // applies the same rule.
        if ($type instanceof FloatType && is_int($value)) {
            return (float)$value;
        }

        return $value;
    }
}
