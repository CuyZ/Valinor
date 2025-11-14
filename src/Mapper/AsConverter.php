<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use Attribute;

/**
 * This attribute can be used to automatically register a converter attribute.
 *
 * When there is no control over the transformer attribute class, the following
 * method can be used: {@see \CuyZ\Valinor\MapperBuilder::registerConverter()}
 *
 * ```
 * namespace My\App;
 *
 * #[\CuyZ\Valinor\Mapper\AsConverter]
 * #[\Attribute(\Attribute::TARGET_PROPERTY)]
 * final class CastToBool
 * {
 *     // @param callable(mixed): bool $next
 *     public function map(string $value, callable $next): bool
 *     {
 *         $value = match ($value) {
 *             'yes', 'on' => true,
 *             'no', 'off' => false,
 *             default => $value,
 *         };
 *
 *         return $next($value);
 *     }
 * }
 *
 * final class User
 * {
 *     public string $name;
 *
 *     #[\My\App\CastToBool]
 *     public bool $isActive;
 * }
 *
 * $user = (new \CuyZ\Valinor\MapperBuilder())
 *     ->mapper()
 *     ->map(User::class, [
 *         'name' => 'John Doe',
 *         'isActive' => 'yes',
 *     ]);
 *
 * $user->name === 'John Doe';
 * $user->isActive === true;
 * ```
 *
 * Attribute converters can also be used on function parameters when mapping
 * arguments:
 *
 * ```
 * function someFunction(string $name, #[\My\App\CastToBool] bool $isActive) {
 *     // …
 * };
 *
 * $arguments = (new \CuyZ\Valinor\MapperBuilder())
 *     ->argumentsMapper()
 *     ->mapArguments(someFunction(...), [
 *         'name' => 'John Doe',
 *         'isActive' => 'yes',
 *     ]);
 *
 * $arguments['name'] === 'John Doe';
 * $arguments['isActive'] === true;
 * ```
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AsConverter {}
