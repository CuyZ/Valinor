<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\ValueDumper;
use LogicException;

/** @internal */
final class UnresolvableType implements Type
{
    public function __construct(
        private string $rawType,
        private string $message,
    ) {}

    public function forProperty(string $signature): self
    {
        return new self(
            $this->rawType,
            "The type `$this->rawType` for property `$signature` could not be resolved: $this->message",
        );
    }

    public function forParameter(string $signature): self
    {
        return new self(
            $this->rawType,
            "The type `$this->rawType` for parameter `$signature` could not be resolved: $this->message",
        );
    }

    public function forFunctionReturnType(string $signature): self
    {
        return new self(
            $this->rawType,
            "The return type `$this->rawType` of function `$signature` could not be resolved: $this->message",
        );
    }

    public function forMethodReturnType(string $signature): self
    {
        return new self(
            $this->rawType,
            "The return type `$this->rawType` of method `$signature` could not be resolved: $this->message",
        );
    }

    public static function forInvalidPropertyDefaultValue(string $signature, Type $type, mixed $defaultValue): self
    {
        $value = ValueDumper::dump($defaultValue);

        return new self(
            $type->toString(),
            "Property `$signature` of type `{$type->toString()}` has invalid default value $value.",
        );
    }

    public static function forInvalidParameterDefaultValue(string $signature, Type $type, mixed $defaultValue): self
    {
        $value = ValueDumper::dump($defaultValue);

        return new self(
            $type->toString(),
            "Parameter `$signature` of type `{$type->toString()}` has invalid default value $value.",
        );
    }

    public static function forNonMatchingPropertyTypes(string $signature, Type $nativeType, Type $docBlockType): self
    {
        return new self(
            $docBlockType->toString(),
            "Types for property `$signature` do not match: `{$docBlockType->toString()}` (docblock) does not accept `{$nativeType->toString()}` (native).",
        );
    }

    public static function forNonMatchingParameterTypes(string $signature, Type $nativeType, Type $docBlockType): self
    {
        return new self(
            $docBlockType->toString(),
            "Types for parameter `$signature` do not match: `{$docBlockType->toString()}` (docblock) does not accept `{$nativeType->toString()}` (native).",
        );
    }

    public static function forNonMatchingFunctionReturnTypes(string $signature, Type $nativeType, Type $docBlockType): self
    {
        return new self(
            $docBlockType->toString(),
            "Return types for function `$signature` do not match: `{$docBlockType->toString()}` (docblock) does not accept `{$nativeType->toString()}` (native).",
        );
    }

    public static function forNonMatchingMethodReturnTypes(string $signature, Type $nativeType, Type $docBlockType): self
    {
        return new self(
            $docBlockType->toString(),
            "Return types for method `$signature` do not match: `{$docBlockType->toString()}` (docblock) does not accept `{$nativeType->toString()}` (native).",
        );
    }

    public static function forLocalAlias(string $raw, string $name, ObjectType $type, InvalidType $exception): self
    {
        return new self(
            $raw,
            "The type `$raw` for local alias `$name` of the class `{$type->className()}` could not be resolved: {$exception->getMessage()}",
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

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        throw new LogicException();
    }

    public function nativeType(): Type
    {
        throw new LogicException();
    }

    public function toString(): string
    {
        return $this->rawType;
    }
}
