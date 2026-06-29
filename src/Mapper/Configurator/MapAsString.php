<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use Stringable;

/**
 * Converts an integer, a float or a `Stringable` object to a `string` before
 * mapping. This is useful when the input data carries numbers that must be
 * handled as strings, for instance an identifier or a postal code.
 *
 * The conversion is applied as an attribute to target a specific property:
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
     * @param callable(string): T $next
     * @return T
     */
    public function map(int|float|Stringable $value, callable $next): mixed
    {
        return $next((string) $value);
    }
}
