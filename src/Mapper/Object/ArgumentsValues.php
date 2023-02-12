<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\InvalidSource;
use CuyZ\Valinor\Type\CompositeTraversableType;
use IteratorAggregate;
use Traversable;

use function array_filter;
use function array_key_exists;
use function count;
use function is_array;

/**
 * @internal
 *
 * @implements IteratorAggregate<Argument>
 */
final class ArgumentsValues implements IteratorAggregate
{
    /** @var array<mixed> */
    private array $value = [];

    private Arguments $arguments;

    private bool $forInterface = false;

    private bool $hadSingleArgument = false;

    private function __construct(Arguments $arguments)
    {
        $this->arguments = $arguments;
    }

    public static function forInterface(Arguments $arguments, mixed $value): self
    {
        $self = new self($arguments);
        $self->forInterface = true;

        if (count($arguments) > 0) {
            $self = $self->transform($value);
        }

        return $self;
    }

    public static function forClass(Arguments $arguments, mixed $value): self
    {
        $self = new self($arguments);
        $self = $self->transform($value);

        return $self;
    }

    public function hasValue(string $name): bool
    {
        return array_key_exists($name, $this->value);
    }

    public function getValue(string $name): mixed
    {
        return $this->value[$name];
    }

    /**
     * @return array<string>
     */
    public function superfluousKeys(): array
    {
        return array_filter(
            array_keys($this->value),
            fn ($key) => ! $this->arguments->has((string)$key)
        );
    }

    public function hadSingleArgument(): bool
    {
        return $this->hadSingleArgument;
    }

    private function transform(mixed $value): self
    {
        $clone = clone $this;

        $transformedValue = $this->transformValueForSingleArgument($value);

        if (! is_array($transformedValue)) {
            throw new InvalidSource($transformedValue, $this->arguments);
        }

        if ($transformedValue !== $value) {
            $clone->hadSingleArgument = true;
        }

        foreach ($this->arguments as $argument) {
            $name = $argument->name();

            if (! array_key_exists($name, $transformedValue) && ! $argument->isRequired()) {
                $transformedValue[$name] = $argument->defaultValue();
            }
        }

        $clone->value = $transformedValue;

        return $clone;
    }

    private function transformValueForSingleArgument(mixed $value): mixed
    {
        if (count($this->arguments) !== 1) {
            return $value;
        }

        $argument = $this->arguments->at(0);
        $name = $argument->name();
        $isTraversable = $argument-> type() instanceof CompositeTraversableType;

        if (is_array($value) && array_key_exists($name, $value)) {
            if ($this->forInterface || ! $isTraversable || count($value) === 1) {
                return $value;
            }
        }

        if ($value === [] && ! $isTraversable) {
            return $value;
        }

        return [$name => $value];
    }

    public function getIterator(): Traversable
    {
        yield from $this->arguments;
    }
}
