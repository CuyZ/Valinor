<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use CuyZ\Valinor\Type\FloatType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function assert;
use function implode;
use function is_array;
use function is_iterable;

/** @internal */
final class Shell
{
    private Settings $settings;

    private Type $type;

    private string $name;

    private bool $hasValue = false;

    private mixed $value = null;

    private Attributes $attributes;

    /** @var list<string> */
    private array $path;

    /** @var list<string> */
    private array $allowedSuperfluousKeys = [];

    /**
     * @param list<string> $path
     */
    private function __construct(Settings $settings, Type $type, array $path = [])
    {
        if ($type instanceof UnresolvableType) {
            throw new UnresolvableShellType($type);
        }

        $this->settings = $settings;
        $this->type = $type;
        $this->path = $path;
    }

    public static function root(
        Settings $settings,
        Type $type,
        mixed $value,
    ): self {
        return (new self($settings, $type))->withValue($value);
    }

    public function child(string $name, Type $type): self
    {
        $path = $this->path;
        $path[] = $name;
        $instance = new self($this->settings, $type, $path);
        $instance->name = $name;

        return $instance;
    }

    public function name(): string
    {
        return $this->name ?? '';
    }

    public function isRoot(): bool
    {
        return ! isset($this->name);
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

    public function withAttributes(Attributes $attributes): self
    {
        $clone = clone $this;
        $clone->attributes = $this->attributes()->merge($attributes);

        return $clone;
    }

    public function allowScalarValueCasting(): bool
    {
        return $this->settings->allowScalarValueCasting;
    }
    public function allowNonSequentialList(): bool
    {
        return $this->settings->allowNonSequentialList;
    }
    public function allowUndefinedValues(): bool
    {
        return $this->settings->allowUndefinedValues;
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

    public function transformIteratorToArray(): self
    {
        if (is_iterable($this->value) && ! is_array($this->value)) {
            $self = clone $this;
            $self->value = iterator_to_array($this->value);

            return $self;
        }

        return $this;
    }

    public function path(): string
    {
        if ($this->isRoot()) {
            return '*root*';
        }

        return implode('.', $this->path);
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
