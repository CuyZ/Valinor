<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\SourceIsNotAnArray;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\CompositeType;
use IteratorAggregate;
use Traversable;

use function array_key_exists;
use function count;
use function is_array;

/**
 * @internal
 *
 * @implements IteratorAggregate<Argument>
 */
final class FilledArguments implements IteratorAggregate
{
    private bool $hasValue;

    /** @var array<mixed> */
    private array $value = [];

    private Arguments $arguments;

    private bool $flexible;

    private function __construct(Arguments $arguments, Shell $shell, bool $flexible)
    {
        $this->arguments = $arguments;
        $this->flexible = $flexible;
        $this->hasValue = $shell->hasValue();
    }

    public static function forInterface(Arguments $arguments, Shell $shell, bool $flexible): self
    {
        $self = new self($arguments, $shell, $flexible);

        if ($self->hasValue) {
            if (count($arguments) > 0) {
                $self->value = $self->transform($shell->value());
            }
        }

        return $self;
    }

    public static function forClass(Arguments $arguments, Shell $shell, bool $flexible): self
    {
        $self = new self($arguments, $shell, $flexible);

        if ($self->hasValue) {
            $self->value = $self->transform($shell->value());
        }

        return $self;
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

    public function has(string $name): bool
    {
        return $this->hasValue && array_key_exists($name, $this->value);
    }

    /**
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->value[$name];
    }

    /**
     * @param mixed $source
     * @return mixed[]
     */
    private function transform($source): array
    {
        $isArray = is_array($source);
        $argumentsCount = count($this->arguments);

        if ($argumentsCount === 1 && $source !== [] && $source !== null) {
            /** @var array<mixed> $source */
            $argument = $this->arguments->at(0);
            $name = $argument->name();
            $type = $argument->type();

            if ($isArray && ! $type instanceof CompositeType) {
                return $source;
            }

            if (! $isArray || ! array_key_exists($name, $source)) {
                return [$name => $source];
            }
        }

        if ($argumentsCount === 0 && $this->flexible && ! $isArray) {
            return [];
        }

        if (! $isArray) {
            throw new SourceIsNotAnArray($source, $this->arguments);
        }

        foreach ($this->arguments as $argument) {
            $name = $argument->name();

            /** @var array<mixed> $source */
            if (! array_key_exists($name, $source) && ! $argument->isRequired()) {
                $source[$name] = $argument->defaultValue();
            }
        }

        /** @var array<mixed> $source */
        return $source;
    }

    public function getIterator(): Traversable
    {
        yield from $this->arguments;
    }
}
