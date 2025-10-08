<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Type\Types\Generics;

/** @internal */
interface Type
{
    /**
     * Should return `true` if the given value is strictly compatible with this
     * type.
     */
    public function accepts(mixed $value): bool;

    /**
     * Compiled version of the `accepts` method.
     */
    public function compiledAccept(ComplianceNode $node): ComplianceNode;

    /**
     * Should return `true` if the given type is strictly compatible with this
     * type.
     *
     * `non-empty-string` matches `string`
     *      —> true, because all non-empty strings are strings
     *
     * `string` matches `non-empty-string`
     *      —> false, because a string is not necessarily non-empty
     *
     * `list<string>` matches `array<scalar>`
     *      —> true, because a list is an array *and* strings are scalars
     *
     * `array<string>` matches `list<scalar>`
     *      —> false, because an array is not necessarily a list
     */
    public function matches(self $other): bool;

    /**
     * Infers the generics of this type from the given type. If no generics can
     * be inferred, the given generics are returned as-is.
     *
     * `array<T>` inferred from `array<string>`
     *      —> T = string
     *
     * `array{foo: T, bar: int}` inferred from `array{foo: string, bar: int}`
     *      —> T = string
     *
     * `T|string` inferred from `positive-int|non-empty-string`
     *      —> T = positive-int
     */
    public function inferGenericsFrom(Type $other, Generics $generics): Generics;

    public function nativeType(): Type;

    public function toString(): string;
}
