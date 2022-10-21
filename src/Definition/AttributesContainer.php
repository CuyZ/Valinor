<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Traversable;

use function array_filter;
use function array_values;
use function count;

/** @internal */
final class AttributesContainer implements Attributes
{
    private static self $empty;

    /** @var array<object> */
    private array $attributes;

    public function __construct(object ...$attributes)
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
            if ($attribute instanceof $className) {
                return true;
            }
        }

        return false;
    }

    public function ofType(string $className): array
    {
        return array_values(array_filter(
            $this->attributes,
            static fn (object $attribute): bool => $attribute instanceof $className
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
        return yield from $this->attributes;
    }
}
