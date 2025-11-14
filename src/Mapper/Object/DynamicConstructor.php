<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use Attribute;
use CuyZ\Valinor\MapperBuilder;

/**
 * This attribute allows the registration of dynamic constructors used when
 * mapping implementations of interfaces or abstract classes.
 *
 * A constructor given to {@see MapperBuilder::registerConstructor()} with this
 * attribute will be called with the first parameter filled with the name of the
 * class the mapper needs to build.
 *
 * Note that the first parameter of the constructor has to be a string otherwise
 * an exception will be thrown on mapping.
 *
 * ```
 * interface SomeInterfaceWithStaticConstructor
 * {
 *     public static function from(string $value): self;
 * }
 *
 * final class SomeClassWithInheritedStaticConstructor implements SomeInterfaceWithStaticConstructor
 * {
 *     private function __construct(private SomeValueObject $value) {}
 *
 *     public static function from(string $value): self
 *     {
 *         return new self(new SomeValueObject($value));
 *     }
 * }
 *
 * (new \CuyZ\Valinor\MapperBuilder())
 *     ->registerConstructor(
 *         #[\CuyZ\Valinor\Attribute\DynamicConstructor]
 *         function (string $className, string $value): SomeInterfaceWithStaticConstructor {
 *             return $className::from($value);
 *         }
 *     )
 *     ->mapper()
 *     ->map(SomeClassWithInheritedStaticConstructor::class, 'foo');
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
final class DynamicConstructor {}
