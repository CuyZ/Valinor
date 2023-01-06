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

    private function __construct(Arguments $arguments)
    {
        $this->arguments = $arguments;
    }

    public static function forInterface(Arguments $arguments, mixed $value): self
    {
        $self = new self($arguments);
        $self->forInterface = true;

        if (count($arguments) > 0) {
            $self->value = $self->transform($value);
        }

        return $self;
    }

    public static function forClass(Arguments $arguments, mixed $value): self
    {
        $self = new self($arguments);
        $self->value = $self->transform($value);

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

    /**
     * @return mixed[]
     */
    private function transform(mixed $value): array
    {
        $value = $this->transformValueForSingleArgument($value);

        if (! is_array($value)) {
            throw new InvalidSource($value, $this->arguments);
        }

        foreach ($this->arguments as $argument) {
            $name = $argument->name();

            if (! array_key_exists($name, $value) && ! $argument->isRequired()) {
                $value[$name] = $argument->defaultValue();
            }
        }

        return $value;
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
