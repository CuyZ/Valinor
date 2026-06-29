<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

use function filter_var;

use const FILTER_VALIDATE_FLOAT;

/**
 * Converts a string representation of a number to a real `float` before
 * mapping. This is useful when the input data carries numbers as strings, for
 * instance a value coming from a form submission or a CSV file.
 *
 * Any value that is not a valid number representation is left untouched and
 * handed over to the mapper, which will raise an error if it cannot be mapped
 * to a float.
 *
 * The conversion is applied as an attribute to target a specific property:
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
     * @param callable(string|float): T $next
     * @return T
     */
    public function map(string $value, callable $next): mixed
    {
        $float = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $next($float === false ? $value : $float);
    }
}
