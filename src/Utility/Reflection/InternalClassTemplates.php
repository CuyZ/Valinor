<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Reflection;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
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

/**
 * Generic signatures of the classes that cannot declare `@template`
 * annotations in their own source code, because they are internal to PHP or
 * provided by an extension.
 *
 * Every template declares a default, so that these classes stay usable without
 * generics: they appear bare in countless existing signatures, which must keep
 * resolving.
 *
 * @internal
 */
final class InternalClassTemplates
{
    /** @var array<class-string, non-empty-string> */
    private const TEMPLATES = [
        ArrayObject::class => "@template TKey of array-key = array-key\n@template TValue = mixed",
        ArrayIterator::class => "@template TKey of array-key = array-key\n@template TValue = mixed",
        SplDoublyLinkedList::class => "@template TValue = mixed",
        SplStack::class => "@template TValue = mixed",
        SplQueue::class => "@template TValue = mixed",
        SplFixedArray::class => "@template TValue = mixed",
        SplHeap::class => "@template TValue = mixed",
        SplMinHeap::class => "@template TValue = mixed",
        SplMaxHeap::class => "@template TValue = mixed",
        SplObjectStorage::class => "@template TObject of object = object\n@template TData = mixed",
        SplPriorityQueue::class => "@template TPriority = mixed\n@template TValue = mixed",
        Generator::class => "@template TKey = mixed\n@template TValue = mixed\n@template TSend = mixed\n@template TReturn = mixed",
        WeakMap::class => "@template TKey of object = object\n@template TValue = mixed",
        WeakReference::class => "@template T of object = object",
        Traversable::class => "@template TKey = mixed\n@template TValue = mixed",
        Iterator::class => "@template TKey = mixed\n@template TValue = mixed",
        IteratorAggregate::class => "@template TKey = mixed\n@template TValue = mixed",
        ArrayAccess::class => "@template TKey = mixed\n@template TValue = mixed",
        Collection::class => "@template TKey = mixed\n@template TValue = mixed",
        Sequence::class => "@template TValue = mixed",
        Vector::class => "@template TValue = mixed",
        Deque::class => "@template TValue = mixed",
        Set::class => "@template TValue = mixed",
        Map::class => "@template TKey = array-key\n@template TValue = mixed",
        Stack::class => "@template TValue = mixed",
        Queue::class => "@template TValue = mixed",
        PriorityQueue::class => "@template TValue = mixed",
        Pair::class => "@template TKey = mixed\n@template TValue = mixed",
    ];

    /**
     * @param class-string $className
     */
    public static function docBlockFor(string $className): ?string
    {
        return self::TEMPLATES[$className] ?? null;
    }
}
