<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use IteratorAggregate;
use Traversable;

use function array_keys;
use function iterator_to_array;

final class ConstructorGenericsMappingTest extends IntegrationTestCase
{
    public function test_constructor_templates_are_bound_to_generics_of_mapped_type(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConstructor(SomeCollectionFactory::create(...))
                ->mapper()
                ->map(SomeCollection::class . '<' . SimpleObject::class . '>', [
                    ['value' => 'foo'],
                    ['value' => 'bar'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        $values = iterator_to_array($result);

        self::assertCount(2, $values);
        self::assertContainsOnlyInstancesOf(SimpleObject::class, $values);
        self::assertSame('foo', $values[0]->value);
        self::assertSame('bar', $values[1]->value);
    }

    public function test_constructor_templates_are_bound_for_nested_property(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConstructor(SomeCollectionFactory::create(...))
                ->mapper()
                ->map(SomeObjectWithCollection::class, [
                    'collection' => [
                        ['value' => 'foo'],
                    ],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        $values = iterator_to_array($result->collection);

        self::assertCount(1, $values);
        self::assertContainsOnlyInstancesOf(SimpleObject::class, $values);
        self::assertSame('foo', $values[0]->value);
    }

    public function test_constructor_templates_are_bound_to_generics_left_to_their_default(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConstructor(SomeDefaultedCollectionFactory::create(...))
                ->mapper()
                ->map(SomeDefaultedCollection::class, ['foo', 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar'], iterator_to_array($result));
    }

    public function test_templates_of_constructor_with_attribute_are_bound_to_generics_of_mapped_type(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(SomeCollectionWithConstructorAttribute::class . '<' . SimpleObject::class . '>', [
                    ['value' => 'foo'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        $values = iterator_to_array($result);

        self::assertCount(1, $values);
        self::assertContainsOnlyInstancesOf(SimpleObject::class, $values);
        self::assertSame('foo', $values[0]->value);
    }

    public function test_templates_of_constructor_with_attribute_are_bound_to_generics_left_to_their_default(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(SomeDefaultedCollectionWithConstructorAttribute::class, ['foo', 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar'], iterator_to_array($result));
    }

    public function test_templates_of_constructor_with_attribute_are_bound_next_to_the_class_generics(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(SomeKeyedCollectionWithConstructorAttribute::class . '<string, ' . SimpleObject::class . '>', [
                    'foo' => ['value' => 'bar'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        $values = iterator_to_array($result);

        self::assertSame(['foo'], array_keys($values));
        self::assertContainsOnlyInstancesOf(SimpleObject::class, $values);
        self::assertSame('bar', $values['foo']->value);
    }
}

/**
 * @template TValue
 * @implements IteratorAggregate<int, TValue>
 */
final class SomeCollectionWithConstructorAttribute implements IteratorAggregate
{
    /** @param list<TValue> $items */
    private function __construct(private array $items) {}

    /**
     * @template T
     * @param list<T> $values
     * @return self<T>
     */
    #[Constructor]
    public static function of(array $values): self
    {
        return new self($values);
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}

/**
 * @template TValue = string
 * @implements IteratorAggregate<int, TValue>
 */
final class SomeDefaultedCollectionWithConstructorAttribute implements IteratorAggregate
{
    /** @param list<TValue> $items */
    private function __construct(private array $items) {}

    /**
     * @template T
     * @param list<T> $values
     * @return self<T>
     */
    #[Constructor]
    public static function of(array $values): self
    {
        return new self($values);
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}

/**
 * The constructor below uses a template of its own next to the generics the
 * class declares, so both have to be known when its signature is resolved.
 *
 * @template TKey of array-key
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
final class SomeKeyedCollectionWithConstructorAttribute implements IteratorAggregate
{
    /** @param array<TKey, TValue> $items */
    private function __construct(private array $items) {}

    /**
     * @template T
     * @param array<TKey, T> $values
     * @return self<TKey, T>
     */
    #[Constructor]
    public static function of(array $values): self
    {
        return new self($values);
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}

/**
 * @template TValue
 * @implements IteratorAggregate<int, TValue>
 */
final class SomeCollection implements IteratorAggregate
{
    /** @param list<TValue> $items */
    public function __construct(private array $items) {}

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}

final class SomeCollectionFactory
{
    /**
     * @template T
     * @param list<T> $values
     * @return SomeCollection<T>
     */
    public static function create(array $values): SomeCollection
    {
        return new SomeCollection($values);
    }
}

final class SomeObjectWithCollection
{
    /** @var SomeCollection<SimpleObject> */
    public SomeCollection $collection;
}

/**
 * @template TValue = string
 * @implements IteratorAggregate<int, TValue>
 */
final class SomeDefaultedCollection implements IteratorAggregate
{
    /** @param list<TValue> $items */
    public function __construct(private array $items) {}

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}

final class SomeDefaultedCollectionFactory
{
    /**
     * @template T
     * @param list<T> $values
     * @return SomeDefaultedCollection<T>
     */
    public static function create(array $values): SomeDefaultedCollection
    {
        return new SomeDefaultedCollection($values);
    }
}
