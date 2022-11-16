<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\InvalidSource;
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

    private function __construct(Arguments $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @param mixed $value
     */
    public static function forInterface(Arguments $arguments, $value): self
    {
        $self = new self($arguments);

        if (count($arguments) > 0) {
            $self->value = $self->transform($value);
        }

        return $self;
    }

    /**
     * @param mixed $value
     */
    public static function forClass(Arguments $arguments, $value): self
    {
        $self = new self($arguments);
        $self->value = $self->transform($value);

        return $self;
    }

    public function hasValue(string $name): bool
    {
        return array_key_exists($name, $this->value);
    }

    /**
     * @return mixed
     */
    public function getValue(string $name)
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
     * @param mixed $value
     * @return mixed[]
     */
    private function transform($value): array
    {
        $isValid = true;

        if (count($this->arguments) === 1 && $value !== []) {
            $argument = $this->arguments->at(0);

            if (! is_array($value) || ! array_key_exists($argument->name(), $value)) {
                return [$argument->name() => $value];
            } elseif (count($value) === 1) {
                $isValid = false;
            }
        }

        if (! $isValid || ! is_array($value)) {
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

    public function getIterator(): Traversable
    {
        yield from $this->arguments;
    }
}
