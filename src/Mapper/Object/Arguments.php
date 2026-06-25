<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use Countable;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use IteratorAggregate;
use Traversable;

use function array_diff_key;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_values;
use function count;
use function is_array;

/**
 * @internal
 *
 * @implements IteratorAggregate<Argument>
 */
final readonly class Arguments implements IteratorAggregate, Countable
{
    /** @var array<string, Argument> */
    private array $arguments;

    private ShapedArrayType $shapedArray;

    public function __construct(Argument ...$arguments)
    {
        $args = [];
        foreach ($arguments as $argument) {
            $args[$argument->name()] = $argument;
        }
        $this->arguments = $args;
        $this->shapedArray = new ShapedArrayType(
            elements: array_map(
                static fn (Argument $argument) => new ShapedArrayElement(
                    key: new StringValueType($argument->name()),
                    type: $argument->type(),
                    optional: ! $argument->isRequired(),
                    attributes: $argument->attributes(),
                ),
                $this->arguments,
            ),
        );
    }

    public static function fromParameters(Parameters $parameters): self
    {
        return new self(...array_map(
            Argument::fromParameter(...),
            [...$parameters],
        ));
    }

    public static function fromProperties(Properties $properties): self
    {
        return new self(...array_map(
            Argument::fromProperty(...),
            [...$properties],
        ));
    }

    public function at(int $index): Argument
    {
        return array_values($this->arguments)[$index];
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->arguments);
    }

    /**
     * @param class-string $className
     */
    public function withoutSelfReferencingSingleArgument(mixed $value, string $className): self
    {
        if (count($this->arguments) !== 1) {
            return $this;
        }

        $argument = $this->at(0);

        if (is_array($value) && array_key_exists($argument->name(), $value)) {
            return $this;
        }

        $type = $argument->type();

        if (! $type instanceof UnionType) {
            return $this;
        }

        $subTypes = $type->types();
        $filtered = array_filter(
            $subTypes,
            static fn (Type $subType) => ! $subType instanceof ObjectType || $subType->className() !== $className,
        );

        if ($filtered === $subTypes) {
            // @infection-ignore-all / No subtype was removed, so rebuilding the
            // union below would yield an equivalent type wrapped in equivalent
            // arguments; this early return only avoids that redundant work.
            return $this;
        }

        return new self($argument->withType(UnionType::from(...$filtered)));
    }

    public function merge(self $other): self
    {
        return new self(
            ...$this->arguments,
            ...array_diff_key($other->arguments, $this->arguments)
        );
    }

    public function toShapedArray(): ShapedArrayType
    {
        return $this->shapedArray;
    }

    /**
     * @return array<string, Argument>
     */
    public function toArray(): array
    {
        return $this->arguments;
    }

    public function count(): int
    {
        return count($this->arguments);
    }

    /**
     * @return Traversable<Argument>
     */
    public function getIterator(): Traversable
    {
        yield from $this->arguments;
    }
}
