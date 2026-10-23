<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\InternalClassTemplates;
use Ds\Collection;
use Ds\Deque;
use Ds\Map;
use Ds\Pair;
use Ds\PriorityQueue;
use Ds\Queue;
use Ds\Sequence;
use Ds\Set;
use Ds\Stack;
use Ds\Vector;
use Generator;
use Iterator;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\DataProvider;
use SplDoublyLinkedList;
use SplFixedArray;
use SplHeap;
use SplMaxHeap;
use SplMinHeap;
use SplObjectStorage;
use SplPriorityQueue;
use SplQueue;
use SplStack;
use Traversable;
use WeakMap;
use WeakReference;

final class InternalClassTemplatesTest extends UnitTestCase
{
    /**
     * Every listed class must be usable without generics, otherwise declaring
     * it generic would turn a valid signature into an unresolvable type.
     *
     * @param class-string $className
     */
    #[DataProvider('internal_classes_data_provider')]
    public function test_internal_class_can_be_used_without_generics(string $className): void
    {
        $type = $this->getService(TypeParser::class)->parse($className);

        self::assertNotInstanceOf(UnresolvableType::class, $type);
        self::assertSame($className, $type->toString());
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('internal_classes_data_provider')]
    public function test_internal_class_can_be_used_with_its_generics(string $className, string $generics): void
    {
        $type = $this->getService(TypeParser::class)->parse("$className<$generics>");

        self::assertNotInstanceOf(UnresolvableType::class, $type);
        self::assertSame("$className<$generics>", $type->toString());
    }

    public function test_doc_block_of_class_absent_from_the_list_is_null(): void
    {
        self::assertNull(InternalClassTemplates::docBlockFor(self::class));
    }

    /**
     * @return iterable<string, array{class-string, string}>
     */
    public static function internal_classes_data_provider(): iterable
    {
        yield 'ArrayObject' => [ArrayObject::class, 'string, int'];
        yield 'ArrayIterator' => [ArrayIterator::class, 'string, int'];
        yield 'SplDoublyLinkedList' => [SplDoublyLinkedList::class, 'int'];
        yield 'SplStack' => [SplStack::class, 'int'];
        yield 'SplQueue' => [SplQueue::class, 'int'];
        yield 'SplFixedArray' => [SplFixedArray::class, 'int'];
        yield 'SplHeap' => [SplHeap::class, 'int'];
        yield 'SplMinHeap' => [SplMinHeap::class, 'int'];
        yield 'SplMaxHeap' => [SplMaxHeap::class, 'int'];
        yield 'SplObjectStorage' => [SplObjectStorage::class, 'object, int'];
        yield 'SplPriorityQueue' => [SplPriorityQueue::class, 'int, string'];
        yield 'Generator' => [Generator::class, 'int, string, bool, float'];
        yield 'WeakMap' => [WeakMap::class, 'object, int'];
        yield 'WeakReference' => [WeakReference::class, 'object'];
        yield 'Traversable' => [Traversable::class, 'int, string'];
        yield 'Iterator' => [Iterator::class, 'int, string'];
        yield 'IteratorAggregate' => [IteratorAggregate::class, 'int, string'];
        yield 'ArrayAccess' => [ArrayAccess::class, 'int, string'];
        yield \Ds\Collection::class => [Collection::class, 'int, string'];
        yield \Ds\Sequence::class => [Sequence::class, 'int'];
        yield \Ds\Vector::class => [Vector::class, 'int'];
        yield \Ds\Deque::class => [Deque::class, 'int'];
        yield \Ds\Set::class => [Set::class, 'int'];
        yield \Ds\Map::class => [Map::class, 'string, int'];
        yield \Ds\Stack::class => [Stack::class, 'int'];
        yield \Ds\Queue::class => [Queue::class, 'int'];
        yield \Ds\PriorityQueue::class => [PriorityQueue::class, 'int'];
        yield \Ds\Pair::class => [Pair::class, 'string, int'];
    }
}
