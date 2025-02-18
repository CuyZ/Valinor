<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use Countable;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
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
final class ArgumentsValues implements IteratorAggregate, Countable
{
    /** @var array<mixed> */
    private array $value = [];

    private Arguments $arguments;

    private bool $hasInvalidValue = false;

    private bool $forInterface = false;

    private bool $hadSingleArgument = false;

    private function __construct(Arguments $arguments)
    {
        $this->arguments = $arguments;
    }

    public static function forInterface(Arguments $arguments, Shell $shell): self
    {
        $self = new self($arguments);
        $self->forInterface = true;

        if (count($arguments) > 0) {
            $self->transform($shell);
        }

        return $self;
    }

    public static function forClass(Arguments $arguments, Shell $shell): self
    {
        $self = new self($arguments);
        $self->transform($shell);

        return $self;
    }

    public function hasInvalidValue(): bool
    {
        return $this->hasInvalidValue;
    }

    public function hasValue(string $name): bool
    {
        return array_key_exists($name, $this->value);
    }

    public function getValue(string $name): mixed
    {
        return $this->value[$name];
    }

    public function hadSingleArgument(): bool
    {
        return $this->hadSingleArgument;
    }

    private function transform(Shell $shell): void
    {
        $value = $shell->value();

        $transformedValue = $this->transformValueForSingleArgument($value, $shell->allowSuperfluousKeys());

        if (! is_array($transformedValue)) {
            $this->hasInvalidValue = true;

            return;
        }

        if ($transformedValue !== $value) {
            $this->hadSingleArgument = true;
        }

        foreach ($this->arguments as $argument) {
            $name = $argument->name();

            if (! array_key_exists($name, $transformedValue) && ! $argument->isRequired()) {
                $transformedValue[$name] = $argument->defaultValue();
            }
        }

        $this->value = $transformedValue;
    }

    private function transformValueForSingleArgument(mixed $value, bool $allowSuperfluousKeys): mixed
    {
        if (count($this->arguments) !== 1) {
            return $value;
        }

        $argument = $this->arguments->at(0);
        $name = $argument->name();
        $type = $argument->type();
        $isTraversableAndAllowsStringKeys = $type instanceof CompositeTraversableType
            && $type->keyType() !== ArrayKeyType::integer();

        if (is_array($value) && array_key_exists($name, $value)) {
            if ($this->forInterface || ! $isTraversableAndAllowsStringKeys || $allowSuperfluousKeys || count($value) === 1) {
                return $value;
            }
        }

        if ($value === [] && ! $isTraversableAndAllowsStringKeys) {
            return $value;
        }

        return [$name => $value];
    }

    public function count(): int
    {
        return count($this->arguments);
    }

    public function getIterator(): Traversable
    {
        yield from $this->arguments;
    }
}
