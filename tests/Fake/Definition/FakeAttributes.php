<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\Attributes;
use stdClass;
use Traversable;

use function array_filter;
use function count;

final class FakeAttributes implements Attributes
{
    /** @var array<object> */
    private array $attributes;

    public function __construct(object ...$attributes)
    {
        $this->attributes = $attributes;
    }

    public static function notEmpty(): self
    {
        return new self(new stdClass());
    }

    public function has(string $className): bool
    {
        return $this->ofType($className) !== [];
    }

    public function ofType(string $className): array
    {
        return array_filter($this->attributes, fn (object $attribute) => $attribute instanceof $className);
    }

    public function count(): int
    {
        return count($this->attributes);
    }

    public function getIterator(): Traversable
    {
        yield from $this->attributes;
    }
}
