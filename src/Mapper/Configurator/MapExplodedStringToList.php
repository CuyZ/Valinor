<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

use function explode;

/**
 * Explodes a string into a list using the given separator before mapping. This
 * is useful when the input data carries a list as a single delimited string,
 * for instance a comma-separated value coming from a CSV file or a query
 * parameter.
 *
 * The resulting list is then mapped against the targeted type, so the items can
 * be cast further, for instance to a `list<int>`.
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\MapExplodedStringToList;
 *
 * final readonly class Product
 * {
 *     public function __construct(
 *         public string $name,
 *
 *         #[MapExplodedStringToList(separator: ',')]
 *         /** @var list<string> *\/
 *         public array $sizes,
 *     ) {}
 * }
 *
 * $product = (new MapperBuilder())
 *     ->mapper()
 *     ->map(Product::class, [
 *         'name' => 'T-Shirt',
 *         'sizes' => 'XS,S,M,L,XL', // mapped to `['XS', 'S', 'M', 'L', 'XL']`
 *     ]);
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
#[AsConverter]
final class MapExplodedStringToList
{
    public function __construct(
        /** @var non-empty-string */
        private string $separator,
    ) {}

    /**
     * @template T
     * @param callable(list<string>): T $next
     * @return T
     */
    public function map(string $value, callable $next): mixed
    {
        return $next(explode($this->separator, $value));
    }
}
