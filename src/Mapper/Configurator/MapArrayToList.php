<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\MapperBuilder;

use function array_values;
use function is_array;
use function iterator_to_array;

/**
 * Discards the keys of an array and maps its values to a list before mapping.
 * This is useful when the input data is an associative array, or a sparse list
 * with missing or out-of-order indices, that should be handled as a sequential
 * list.
 *
 * The conversion only applies when the target type is a list; other array or
 * iterable targets keep their keys untouched.
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
 * final readonly class Basket
 * {
 *     public function __construct(
 *         /** @var list<string> *\/
 *         public array $products,
 *     ) {}
 * }
 *
 * $basket = (new MapperBuilder())
 *     ->allowNonSequentialList()
 *     ->mapper()
 *     ->map(Basket::class, [
 *         'a' => 'Coffee',
 *         'b' => 'Tea',
 *     ]); // mapped to `['Coffee', 'Tea']`
 * ```
 *
 * Local usage as an attribute
 * ---------------------------
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\MapArrayToList;
 *
 * final readonly class Basket
 * {
 *     public function __construct(
 *         #[MapArrayToList]
 *         /** @var list<string> *\/
 *         public array $products,
 *     ) {}
 * }
 *
 * $basket = (new MapperBuilder())
 *     ->mapper()
 *     ->map(Basket::class, [
 *         'a' => 'Coffee',
 *         'b' => 'Tea',
 *     ]); // mapped to `['Coffee', 'Tea']`
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
#[AsConverter]
final class MapArrayToList implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder->registerConverter($this->map(...));
    }

    /**
     * The template bound restricts the converter to list targets: a concrete
     * `list` return type would also match `array`, `iterable` or `mixed`
     * targets (a list is a subtype of all of them) and would discard the keys
     * of values that are already valid for them.
     *
     * @template T of list<mixed>
     * @param iterable<mixed> $value
     * @param callable(list<mixed>): T $next
     * @return T
     */
    public function map(iterable $value, callable $next): array
    {
        return $next(is_array($value) ? array_values($value) : iterator_to_array($value, preserve_keys: false));
    }
}
