<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use Stringable;

use function is_numeric;
use function is_string;

/**
 * Converts an integer, a float or a `Stringable` object to a `string` before
 * mapping. This is useful when the input data carries numbers that must be
 * handled as strings, for instance an identifier or a postal code.
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
 * final readonly class User
 * {
 *     public function __construct(
 *         public string $name,
 *         public string $id,
 *     ) {}
 * }
 *
 * $user = (new MapperBuilder())
 *     ->allowCastingToString()
 *     ->mapper()
 *     ->map(User::class, [
 *         'name' => 'John Doe',
 *         'id' => 42, // mapped to `'42'`
 *     ]);
 * ```
 *
 * Local usage as an attribute
 * ---------------------------
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\MapAsString;
 *
 * final readonly class User
 * {
 *     public function __construct(
 *         public string $name,
 *
 *         #[MapAsString]
 *         public string $id,
 *     ) {}
 * }
 *
 * $user = (new MapperBuilder())
 *     ->mapper()
 *     ->map(User::class, [
 *         'name' => 'John Doe',
 *         'id' => 42, // mapped to `'42'`
 *     ]);
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
#[AsConverter]
final class MapAsString
{
    /**
     * @template T of string
     * @param callable(mixed): T $next
     * @return T
     */
    public function map(int|float|Stringable $value, callable $next): mixed
    {
        return $next((string) $value);
    }

    public static function convert(mixed $value): mixed
    {
        if (is_string($value) || is_numeric($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return $value;
    }
}
