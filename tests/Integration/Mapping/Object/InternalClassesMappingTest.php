<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use ArrayIterator;
use ArrayObject;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use Ds\Deque;
use Ds\Map;
use Ds\Pair;
use Ds\Queue;
use Ds\Set;
use Ds\Stack;
use Ds\Vector;
use PHPUnit\Framework\Attributes\DataProvider;
use SplDoublyLinkedList;
use SplFixedArray;
use SplMaxHeap;
use SplMinHeap;
use SplQueue;
use SplStack;
use Traversable;

use function iterator_to_array;
use function reset;

final class InternalClassesMappingTest extends IntegrationTestCase
{
    /**
     * @param class-string $className
     * @param list<string> $expected
     */
    #[DataProvider('list_shaped_internal_classes_data_provider')]
    public function test_list_shaped_internal_class_is_built_from_mapped_values(string $className, array $expected): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map($className . '<string>', ['a', 'b']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf($className, $result);
        self::assertInstanceOf(Traversable::class, $result);
        self::assertSame($expected, iterator_to_array($result, false));
    }

    public function test_array_object_is_built_with_its_keys(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(ArrayObject::class . '<string, ' . SimpleObject::class . '>', [
                    'foo' => ['value' => 'foo value'],
                    'bar' => ['value' => 'bar value'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        $values = iterator_to_array($result);

        self::assertCount(2, $values);
        self::assertContainsOnlyInstancesOf(SimpleObject::class, $values);
        self::assertSame('foo value', $values['foo']->value);
        self::assertSame('bar value', $values['bar']->value);
    }

    public function test_array_iterator_is_built_with_its_keys(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(ArrayIterator::class . '<string, string>', ['foo' => 'foo value']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo' => 'foo value'], iterator_to_array($result));
    }

    public function test_ds_map_is_built_with_its_keys(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(Map::class . '<string, string>', ['foo' => 'foo value']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo' => 'foo value'], $result->toArray());
    }

    public function test_ds_pair_is_built_from_its_key_and_value(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(Pair::class . '<string, int>', ['key' => 'foo', 'value' => 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->key);
        self::assertSame(42, $result->value);
    }

    public function test_internal_classes_are_built_as_nested_properties(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(SomeObjectWithInternalClasses::class, [
                    'values' => ['foo' => ['value' => 'foo value']],
                    'queue' => [['value' => 'bar value']],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        $value = $result->values['foo'];

        self::assertInstanceOf(SimpleObject::class, $value);
        self::assertSame('foo value', $value->value);
        self::assertSame('bar value', $result->queue->dequeue()->value);
    }

    public function test_registered_constructor_takes_precedence_over_built_in_one(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConstructor(OverridingArrayObjectFactory::create(...))
                ->mapper()
                ->map(ArrayObject::class . '<string, string>', ['foo' => 'foo value']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['overridden' => 'foo value'], iterator_to_array($result));
    }

    public function test_internal_class_without_generics_is_built_when_permissive_types_are_allowed(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowPermissiveTypes()
                ->mapper()
                ->map(SplStack::class, ['a', 'b']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['b', 'a'], iterator_to_array($result, false));
    }

    /**
     * @return iterable<string, array{class-string, list<string>}>
     */
    public static function list_shaped_internal_classes_data_provider(): iterable
    {
        yield 'SplDoublyLinkedList' => [SplDoublyLinkedList::class, ['a', 'b']];
        yield 'SplStack' => [SplStack::class, ['b', 'a']];
        yield 'SplQueue' => [SplQueue::class, ['a', 'b']];
        yield 'SplFixedArray' => [SplFixedArray::class, ['a', 'b']];
        yield 'SplMinHeap' => [SplMinHeap::class, ['a', 'b']];
        yield 'SplMaxHeap' => [SplMaxHeap::class, ['b', 'a']];
        yield \Ds\Vector::class => [Vector::class, ['a', 'b']];
        yield \Ds\Deque::class => [Deque::class, ['a', 'b']];
        yield \Ds\Set::class => [Set::class, ['a', 'b']];
        yield \Ds\Stack::class => [Stack::class, ['b', 'a']];
        yield \Ds\Queue::class => [Queue::class, ['a', 'b']];
    }
}

final class SomeObjectWithInternalClasses
{
    /** @var ArrayObject<string, SimpleObject> */
    public ArrayObject $values;

    /** @var SplQueue<SimpleObject> */
    public SplQueue $queue;
}

final class OverridingArrayObjectFactory
{
    /**
     * @param non-empty-array<string, string> $values
     * @return ArrayObject<string, string>
     */
    public static function create(array $values): ArrayObject
    {
        return new ArrayObject(['overridden' => reset($values)]);
    }
}
