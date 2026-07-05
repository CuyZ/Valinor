<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

/**
 * Feeds a class property, or a constructor/method argument, from a specific
 * source key, instead of matching it against the property/argument name.
 *
 * This is useful when the source data uses a key that differs from the name of
 * the property it should be mapped to:
 *
 * The given key is used as-is: it is *not* affected by the key converters
 * registered with {@see \CuyZ\Valinor\MapperBuilder::registerKeyConverter()},
 * nor does the property name remain accepted as a fallback. The source is read
 * only from the given key.
 *
 * ```
 * final class Person
 * {
 *     public function __construct(
 *         public string $name,
 *         #[\CuyZ\Valinor\Mapper\Configurator\MapFromKey('zipCode')]
 *         public string $postalCode,
 *     ) {}
 * }
 *
 * $person = (new \CuyZ\Valinor\MapperBuilder())
 *     ->mapper()
 *     ->map(Person::class, [
 *         'name' => 'John Doe',
 *         'zipCode' => '75001',
 *     ]);
 *
 * $person->postalCode === '75001';
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
#[AsConverter]
final class MapFromKey
{
    public function __construct(
        /** @var non-empty-string */
        private string $key,
    ) {}

    public function mapKey(): string
    {
        return $this->key;
    }
}
