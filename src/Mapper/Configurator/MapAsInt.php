<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

use function filter_var;
use function is_string;
use function ltrim;

use const FILTER_VALIDATE_INT;

/**
 * Converts a string representation of an integer to a real `int` before mapping.
 * This is useful when the input data carries numbers as strings, for instance a
 * value coming from a form submission or a CSV file.
 *
 * Any value that is not a valid integer representation is left untouched and
 * handed over to the mapper, which will raise an error if it cannot be mapped to
 * an integer.
 *
 * The conversion is applied as an attribute to target a specific property:
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\MapAsInt;
 *
 * final readonly class User
 * {
 *     public function __construct(
 *         public string $name,
 *
 *         #[MapAsInt]
 *         public int $age,
 *     ) {}
 * }
 *
 * $user = (new MapperBuilder())
 *     ->mapper()
 *     ->map(User::class, [
 *         'name' => 'John Doe',
 *         'age' => '42', // mapped to `42`
 *     ]);
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
#[AsConverter]
final class MapAsInt
{
    /**
     * @template T of int
     * @param callable(string|int|float): T $next
     * @return T
     */
    public function map(string|int|float $value, callable $next): mixed
    {
        // Leading zeros are stripped for string inputs so that values such as
        // "040" are recognized as integers.
        $normalized = is_string($value) && $value !== '' ? (ltrim($value, '0') ?: '0') : $value;

        $int = filter_var($normalized, FILTER_VALIDATE_INT);

        return $next($int === false ? $value : $int);
    }
}
