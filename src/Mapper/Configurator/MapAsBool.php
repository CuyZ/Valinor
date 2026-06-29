<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

use function in_array;

/**
 * Converts the given string and integer representations to a real `bool` before
 * mapping. This is useful when the input data uses values such as `'1'` or
 * `'true'` to express a boolean.
 *
 * By default, the following values are recognized:
 *
 * - `true`: `1`, `'1'` and `'true'`
 * - `false`: `0`, `'0'` and `'false'`
 *
 * Any other value is left untouched and handed over to the mapper, which will
 * raise an error if it cannot be mapped to a boolean.
 *
 * The conversion is applied as an attribute to target a specific property:
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\MapAsBool;
 *
 * final readonly class User
 * {
 *     public function __construct(
 *         public string $name,
 *
 *         #[MapAsBool]
 *         public bool $isActive,
 *     ) {}
 * }
 *
 * $user = (new MapperBuilder())
 *     ->mapper()
 *     ->map(User::class, [
 *         'name' => 'John Doe',
 *         'isActive' => 'true', // mapped to `true`
 *     ]);
 * ```
 *
 * The accepted representations can be customized by giving the values that
 * should be converted to `true` and `false`, for instance to also recognize
 * `'on'` and `'off'`:
 *
 * ```
 * #[MapAsBool(true: ['on', 'yes'], false: ['off', 'no'])]
 * public bool $isActive;
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
#[AsConverter]
final class MapAsBool
{
    public function __construct(
        /** @var non-empty-list<non-empty-string|int> */
        private array $true = [1, '1', 'true'],
        /** @var non-empty-list<non-empty-string|int> */
        private array $false = [0, '0', 'false'],
    ) {}

    /**
     * @template T of bool
     * @param callable(bool|int|string): T $next
     * @return T
     */
    public function map(string|int $value, callable $next): mixed
    {
        return $next(match (true) {
            in_array($value, $this->true, true) => true,
            in_array($value, $this->false, true) => false,
            default => $value,
        });
    }
}
