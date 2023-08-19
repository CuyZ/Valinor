<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Traversable;

use function array_filter;
use function array_map;
use function array_values;
use function count;
use function is_a;

/**
 * @phpstan-type AttributeParam = array{class: class-string, callback: callable(): object}
 *
 * @internal
 */
final class AttributesContainer implements Attributes
{
    private static self $empty;

    /** @var list<AttributeParam> */
    private array $attributes;

    /**
     * @no-named-arguments
     * @param AttributeParam ...$attributes
     */
    public function __construct(array ...$attributes)
    {
        $this->attributes = $attributes;
    }

    public static function empty(): self
    {
        return self::$empty ??= new self();
    }

    public function has(string $className): bool
    {
        foreach ($this->attributes as $attribute) {
            if (is_a($attribute['class'], $className, true)) {
                return true;
            }
        }

        return false;
    }

    public function ofType(string $className): array
    {
        $attributes = array_filter(
            $this->attributes,
            static fn (array $attribute): bool => is_a($attribute['class'], $className, true)
        );

        /** @phpstan-ignore-next-line  */
        return array_values(array_map(
            fn (array $attribute) => $attribute['callback'](),
            $attributes
        ));
    }

    public function count(): int
    {
        return count($this->attributes);
    }

    /**
     * @return Traversable<object>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->attributes as $attribute) {
            yield $attribute['callback']();
        }
    }
}
