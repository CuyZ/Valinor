<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Types\ArrayKeyType;

use function array_key_exists;
use function count;
use function is_array;

/** @internal */
final class ArgumentsValues
{
    public function __construct(
        public readonly Shell $shell,
        private string|null $singleArgumentName = null,
    ) {}

    public static function forInterface(Shell $shell, Arguments $arguments): self
    {
        $shell = $shell->allowSuperfluousKeys();

        if (count($arguments) === 0) {
            return new self(
                $shell->withValue([])->withType($arguments->toShapedArray()),
            );
        }

        return self::forClass($shell, $arguments);
    }

    /**
     * This transforms the arguments of an object constructor to a shaped array
     * equivalent. This shaped array is then given back to the mapper to ensure
     * the source is mapped by respecting the wanted structure.
     *
     * Example:
     *
     * ```php
     * final readonly class User
     * {
     *     public function __construct(
     *         public string $name,
     *         public DateTimeInterface $birthDate,
     *         public string|null $email = null,
     *     ) {}
     * }
     *
     * // This class is represented as:
     * // array{name: string, birthDate: DateTimeInterface, email?: string|null}
     * ```
     */
    public static function forClass(Shell $shell, Arguments $arguments): self
    {
        if ($shell->allowUndefinedValues && $shell->value() === null) {
            $shell = $shell->withValue([]);
        } elseif (is_iterable($shell->value()) && ! is_array($shell->value())) {
            $shell = $shell->withValue(iterator_to_array($shell->value()));
        }

        if (count($arguments) !== 1) {
            return new self($shell->withType($arguments->toShapedArray()));
        }

        $argument = $arguments->at(0);
        $name = $argument->name();
        $type = $argument->type();
        $attributes = $argument->attributes();

        $isTraversableAndAllowsStringKeys = $type instanceof CompositeTraversableType
            && $type->keyType() !== ArrayKeyType::integer();

        if (is_array($shell->value()) && array_key_exists($name, $shell->value())) {
            if (! $isTraversableAndAllowsStringKeys || $shell->allowSuperfluousKeys || count($shell->value()) === 1) {
                return new self($shell->withType($arguments->toShapedArray()));
            }
        }

        if ($shell->value() === [] && ! $isTraversableAndAllowsStringKeys) {
            return new self($shell->withType($arguments->toShapedArray()));
        }

        // If we get there, it means a scalar argument was given where an array
        // with a single value was awaited. We purposely flatten the shell
        // structure to allow the mapper to do its job. Note that the method
        // `transform()` below allows to get back the desired structure, with
        // the mapped value.
        return new self(
            shell: $shell->withType($type)->withAttributes($attributes),
            singleArgumentName: $argument->name(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(mixed $value): array
    {
        if ($this->singleArgumentName) {
            return [$this->singleArgumentName => $value];
        }

        /** @var array<string, mixed> we know at this point this is an array */
        return $value;
    }
}
