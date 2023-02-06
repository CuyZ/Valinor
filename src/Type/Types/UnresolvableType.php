<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\ValueDumper;
use LogicException;

/** @internal */
final class UnresolvableType extends LogicException implements Type
{
    private string $rawType;

    public function __construct(string $rawType, string $message)
    {
        $this->rawType = $rawType;

        parent::__construct($message);
    }

    public static function forProperty(string $raw, string $signature, InvalidType $exception): self
    {
        return new self(
            $raw,
            "The type `$raw` for property `$signature` could not be resolved: {$exception->getMessage()}"
        );
    }

    public static function forParameter(string $raw, string $signature, InvalidType $exception): self
    {
        return new self(
            $raw,
            "The type `$raw` for parameter `$signature` could not be resolved: {$exception->getMessage()}"
        );
    }

    public static function forMethodReturnType(string $raw, string $signature, InvalidType $exception): self
    {
        return new self(
            $raw,
            "The type `$raw` for return type of method `$signature` could not be resolved: {$exception->getMessage()}"
        );
    }

    public static function forInvalidPropertyDefaultValue(string $signature, Type $type, mixed $defaultValue): self
    {
        $value = ValueDumper::dump($defaultValue);

        return new self(
            $type->toString(),
            "Property `$signature` of type `{$type->toString()}` has invalid default value $value."
        );
    }

    public static function forInvalidParameterDefaultValue(string $signature, Type $type, mixed $defaultValue): self
    {
        $value = ValueDumper::dump($defaultValue);

        return new self(
            $type->toString(),
            "Parameter `$signature` of type `{$type->toString()}` has invalid default value $value."
        );
    }

    public static function forLocalAlias(string $raw, string $name, ClassType $type, InvalidType $exception): self
    {
        return new self(
            $raw,
            "The type `$raw` for local alias `$name` of the class `{$type->className()}` could not be resolved: {$exception->getMessage()}"
        );
    }

    public function accepts(mixed $value): bool
    {
        throw $this;
    }

    public function matches(Type $other): bool
    {
        throw $this;
    }

    public function toString(): string
    {
        return $this->rawType;
    }
}
