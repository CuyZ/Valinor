<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\InvalidSource;
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

    public static function forInterface(Arguments $arguments, Shell $shell): self
    {
        $self = new self($arguments);
        $self->forInterface = true;

        if (count($arguments) > 0) {
            $self = $self->transform($shell);
        }

        return $self;
    }

    public static function forClass(Arguments $arguments, Shell $shell): self
    {
        $self = new self($arguments);
        $self = $self->transform($shell);

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

    public function hadSingleArgument(): bool
    {
        return $this->hadSingleArgument;
    }

    private function transform(Shell $shell): self
    {
        $clone = clone $this;

        $transformedValue = $this->transformValueForSingleArgument($shell);

        if (! is_array($transformedValue)) {
            throw new InvalidSource($transformedValue, $this->arguments);
        }

        if ($transformedValue !== $shell->value()) {
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

    private function transformValueForSingleArgument(Shell $shell): mixed
    {
        $value = $shell->value();

        if (count($this->arguments) !== 1) {
            return $value;
        }

        $argument = $this->arguments->at(0);
        $name = $argument->name();
        $type = $argument->type();
        $isTraversableAndAllowsStringKeys = $type instanceof CompositeTraversableType
            && $type->keyType() !== ArrayKeyType::integer();

        if (is_array($value) && array_key_exists($name, $value)) {
            if ($this->forInterface || ! $isTraversableAndAllowsStringKeys || $shell->allowSuperfluousKeys() || count($value) === 1) {
                return $value;
            }
        }

        if ($value === [] && ! $isTraversableAndAllowsStringKeys) {
            return $value;
        }

        return [$name => $value];
    }

    public function getIterator(): Traversable
    {
        yield from $this->arguments;
    }
}
