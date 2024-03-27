<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use CuyZ\Valinor\Utility\ValueDumper;
use LogicException;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;

/** @internal */
final class UnresolvableType implements Type
{
    public function __construct(
        private string $rawType,
        private string $message,
    ) {}

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

    public static function forDocBlockTypeNotMatchingNative(ReflectionProperty|ReflectionParameter|ReflectionFunctionAbstract $reflection, Type $typeFromDocBlock, Type $typeFromReflection): self
    {
        $signature = Reflection::signature($reflection);

        if ($reflection instanceof ReflectionProperty) {
            $message = "Types for property `$signature` do not match: `{$typeFromDocBlock->toString()}` (docblock) does not accept `{$typeFromReflection->toString()}` (native).";
        } elseif ($reflection instanceof ReflectionParameter) {
            $message = "Types for parameter `$signature` do not match: `{$typeFromDocBlock->toString()}` (docblock) does not accept `{$typeFromReflection->toString()}` (native).";
        } else {
            $message = "Return types for method `$signature` do not match: `{$typeFromDocBlock->toString()}` (docblock) does not accept `{$typeFromReflection->toString()}` (native).";
        }

        return new self($typeFromDocBlock->toString(), $message);
    }

    public static function forLocalAlias(string $raw, string $name, ObjectType $type, InvalidType $exception): self
    {
        return new self(
            $raw,
            "The type `$raw` for local alias `$name` of the class `{$type->className()}` could not be resolved: {$exception->getMessage()}"
        );
    }

    public function message(): string
    {
        return $this->message;
    }

    public function accepts(mixed $value): bool
    {
        throw new LogicException();
    }

    public function matches(Type $other): bool
    {
        throw new LogicException();
    }

    public function toString(): string
    {
        return $this->rawType;
    }
}
