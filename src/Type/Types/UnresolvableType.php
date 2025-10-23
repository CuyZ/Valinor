<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\VacantType;
use CuyZ\Valinor\Utility\ValueDumper;
use LogicException;

use function array_map;
use function implode;
use function lcfirst;

/** @internal */
final class UnresolvableType implements VacantType
{
    public function __construct(
        private string $rawType,
        private string $message,
    ) {}

    public function forProperty(string $signature): self
    {
        return new self(
            $this->rawType,
            "The type `$this->rawType` for property `$signature` could not be resolved: " . lcfirst($this->message),
        );
    }

    public function forParameter(string $signature): self
    {
        return new self(
            $this->rawType,
            "The type `$this->rawType` for parameter `$signature` could not be resolved: " . lcfirst($this->message),
        );
    }

    public function forFunctionReturnType(string $signature): self
    {
        return new self(
            $this->rawType,
            "The return type `$this->rawType` of function `$signature` could not be resolved: " . lcfirst($this->message),
        );
    }

    public function forMethodReturnType(string $signature): self
    {
        return new self(
            $this->rawType,
            "The return type `$this->rawType` of method `$signature` could not be resolved: " . lcfirst($this->message),
        );
    }

    public static function forInvalidDefaultValue(Type $type, mixed $defaultValue): self
    {
        $value = ValueDumper::dump($defaultValue);

        return new self(
            $type->toString(),
            "Invalid default value $value.",
        );
    }

    public static function forNonMatchingTypes(Type $nativeType, Type $docBlockType): self
    {
        return new self(
            $docBlockType->toString(),
            "`{$docBlockType->toString()}` (docblock) does not accept `{$nativeType->toString()}` (native).",
        );
    }

    public function forLocalAlias(string $raw, string $name, ObjectType $type): self
    {
        return new self(
            $raw,
            "The type `$raw` for local alias `$name` of the class `{$type->className()}` could not be resolved: " . lcfirst($this->message),
        );
    }

    public static function forDuplicatedTemplateName(string $signature, string $template): self
    {
        return new self(
            $template,
            "The template `$template` in `$signature` was defined at least twice."
        );
    }

    public static function forClassTypeAliasesCollision(string $alias, int $numberOfCollisions): self
    {
        return new self(
            $alias,
            "Collision for the type alias `$alias` that was declared $numberOfCollisions times."
        );
    }

    public static function forInvalidAliasImportClassType(string $className, string $alias, string $rawType): self
    {
        return new self(
            $alias,
            "Invalid type alias import `$alias` in class `$className`, a valid class name is expected but `$rawType` was given."
        );
    }

    public static function forUnknownTypeAliasImport(ObjectType $type, string $importClassName, string $alias): self
    {
        return new self(
            $alias,
            "Type alias `$alias` imported in `{$type->className()}` could not be found in `$importClassName`"
        );
    }

    public static function forInvalidAssignedGeneric(Type $generic, Type $template, string $name, string $className): self
    {
        return new self(
            $generic->toString(),
            "The generic `{$generic->toString()}` is not a subtype of `{$template->toString()}` for the template `$name` of the class `$className`.",
        );
    }

    public function forInvalidTemplateType(string $signature, string $template): self
    {
        return new self(
            $this->rawType,
            "Invalid template `$template` for `$signature`: " . lcfirst($this->message),
        );
    }

    public static function forSeveralExtendTagsFound(string $className): self
    {
        return new self(
            $className,
            "Only one `@extends` tag should be set for the class `$className`."
        );
    }

    public static function forInvalidExtendTagType(string $className, string $parentClassName, Type $invalidExtendTag): self
    {
        return new self(
            $className,
            "The `@extends` tag of the class `$className` has invalid type `{$invalidExtendTag->toString()}`, it should be `$parentClassName`.",
        );
    }

    public function forExtendTagTypeError(string $className): self
    {
        return new self(
            $this->rawType,
            "The `@extends` tag of the class `$className` is not valid: " . lcfirst($this->message),
        );
    }

    /**
     * @param non-empty-list<Type> $keyTypes
     */
    public function forArrayType(string $arrayType, array $keyTypes, Type $subType): self
    {
        $keyTypes = implode('|', array_map(static fn (Type $type) => $type->toString(), $keyTypes));
        $signature = "$arrayType<$keyTypes, {$subType->toString()}>";

        return new self(
            $signature,
            "Invalid type `$signature`: " . lcfirst($this->message),
        );
    }

    public function accepts(mixed $value): bool
    {
        throw new LogicException();
    }

    public function matches(Type $other): bool
    {
        throw new LogicException();
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
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

    public static function forSuperfluousValue(string $key): self
    {
        return new self('*none*', "Unexpected key `$key`.");
    }

    public function message(): string
    {
        return $this->message;
    }

    public function symbol(): string
    {
        return $this->rawType;
    }

    public function toString(): string
    {
        return $this->rawType;
    }
}
