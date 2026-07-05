<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

use function is_numeric;

/**
 * Converts a string representation of a number to a real `float` before
 * mapping. This is useful when the input data carries numbers as strings, for
 * instance a value coming from a form submission or a CSV file.
 *
 * Any value that is not a valid number representation is left untouched and
 * handed over to the mapper, which will raise an error if it cannot be mapped
 * to a float.
 *
 * This conversion can be applied globally, or as an attribute to target a
 * specific property.
 *
 * Global usage
 * ------------
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 *
 * final readonly class Product
 * {
 *     public function __construct(
 *         public string $name,
 *         public float $price,
 *     ) {}
 * }
 *
 * $product = (new MapperBuilder())
 *     ->allowCastingToFloat()
 *     ->mapper()
 *     ->map(Product::class, [
 *         'name' => 'Coffee',
 *         'price' => '4.50', // mapped to `4.5`
 *     ]);
 * ```
 *
 * Local usage as an attribute
 * ---------------------------
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\MapAsFloat;
 *
 * final readonly class Product
 * {
 *     public function __construct(
 *         public string $name,
 *
 *         #[MapAsFloat]
 *         public float $price,
 *     ) {}
 * }
 *
 * $product = (new MapperBuilder())
 *     ->mapper()
 *     ->map(Product::class, [
 *         'name' => 'Coffee',
 *         'price' => '4.50', // mapped to `4.5`
 *     ]);
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
#[AsConverter]
final class MapAsFloat
{
    /**
     * @template T of float
     * @param callable(mixed): T $next
     * @return T
     */
    public function map(string $value, callable $next): mixed
    {
        return $next(self::convert($value));
    }

    public static function convert(mixed $value): mixed
    {
        if (! is_numeric($value)) {
            return $value;
        }

        return (float)$value;
    }
}
