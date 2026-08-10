<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use ArrayIterator;
use ArrayObject;
use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Exception\CannotParseToDateTime;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Ds\Deque;
use Ds\Map;
use Ds\Pair;
use Ds\Queue;
use Ds\Set;
use Ds\Stack;
use Ds\Vector;
use Exception;
use SplDoublyLinkedList;
use SplFixedArray;
use SplMaxHeap;
use SplMinHeap;
use SplQueue;
use SplStack;

use function array_key_exists;

/**
 * A constructor is given only when it is asked for, one closure at a time, so
 * that a mapper initialisation pays nothing for the classes a mapping never
 * touches.
 *
 * @internal
 */
final class InternalClassConstructors
{
    /** @var array<class-string, null> */
    public const CLASSES = [
        DateTime::class => null,
        DateTimeImmutable::class => null,
        DateTimeZone::class => null,
        ArrayObject::class => null,
        ArrayIterator::class => null,
        SplDoublyLinkedList::class => null,
        SplStack::class => null,
        SplQueue::class => null,
        SplFixedArray::class => null,
        SplMinHeap::class => null,
        SplMaxHeap::class => null,
        Vector::class => null,
        Deque::class => null,
        Set::class => null,
        Map::class => null,
        Stack::class => null,
        Queue::class => null,
        Pair::class => null,
    ];

    public function __construct(
        private FunctionDefinitionRepository $functionDefinitionRepository,
        /** @var non-empty-list<non-empty-string> */
        private array $supportedDateFormats,
    ) {}

    /**
     * @param class-string $className
     *
     * @phpstan-assert-if-true key-of<self::CLASSES> $className
     */
    public function has(string $className): bool
    {
        return array_key_exists($className, self::CLASSES);
    }

    /**
     * @param key-of<self::CLASSES> $className
     */
    public function get(string $className): FunctionObject
    {
        $callable = $this->callableFor($className);

        return new FunctionObject(
            $this->functionDefinitionRepository->for($callable),
            $callable,
        );
    }

    /**
     * @param key-of<self::CLASSES> $className
     */
    private function callableFor(string $className): callable
    {
        $supportedDateFormats = $this->supportedDateFormats;

        return match ($className) {
            /** @param non-empty-string|int|float $value */
            DateTime::class => static fn (string|int|float $value): DateTime => self::dateTime(DateTime::class, $value, $supportedDateFormats),
            /** @param non-empty-string|int|float $value */
            DateTimeImmutable::class => static fn (string|int|float $value): DateTimeImmutable => self::dateTime(DateTimeImmutable::class, $value, $supportedDateFormats),
            DateTimeZone::class => self::dateTimeZone(...),
            ArrayObject::class => self::arrayObject(...),
            ArrayIterator::class => self::arrayIterator(...),
            SplDoublyLinkedList::class => self::splDoublyLinkedList(...),
            SplStack::class => self::splStack(...),
            SplQueue::class => self::splQueue(...),
            SplFixedArray::class => self::splFixedArray(...),
            SplMinHeap::class => self::splMinHeap(...),
            SplMaxHeap::class => self::splMaxHeap(...),
            Vector::class => self::dsVector(...),
            Deque::class => self::dsDeque(...),
            Set::class => self::dsSet(...),
            Map::class => self::dsMap(...),
            Stack::class => self::dsStack(...),
            Queue::class => self::dsQueue(...),
            Pair::class => self::dsPair(...),
        };
    }

    /**
     * @template T of DateTime|DateTimeImmutable
     * @param class-string<T> $className
     * @param non-empty-list<non-empty-string> $formats
     * @return T
     */
    private static function dateTime(string $className, string|int|float $value, array $formats): DateTime|DateTimeImmutable
    {
        foreach ($formats as $format) {
            $date = $className::createFromFormat($format, (string)$value) ?: null;

            if ($date) {
                return $date;
            }
        }

        throw new CannotParseToDateTime($formats);
    }

    public static function dateTimeZone(string $timezone): DateTimeZone
    {
        try {
            return new DateTimeZone($timezone);
        } catch (Exception) {
            throw MessageBuilder::newError('Value {source_value} is not a valid timezone.')
                ->withCode('invalid_timezone')
                ->build();
        }
    }

    /**
     * @template TKey of array-key
     * @template TValue
     * @param array<TKey, TValue> $values
     * @return ArrayObject<TKey, TValue>
     */
    public static function arrayObject(array $values): ArrayObject
    {
        return new ArrayObject($values);
    }

    /**
     * @template TKey of array-key
     * @template TValue
     * @param array<TKey, TValue> $values
     * @return ArrayIterator<TKey, TValue>
     */
    public static function arrayIterator(array $values): ArrayIterator
    {
        return new ArrayIterator($values);
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return SplDoublyLinkedList<TValue>
     */
    public static function splDoublyLinkedList(array $values): SplDoublyLinkedList
    {
        /** @var SplDoublyLinkedList<TValue> $list */
        $list = new SplDoublyLinkedList();

        foreach ($values as $value) {
            $list->push($value);
        }

        return $list;
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return SplStack<TValue>
     */
    public static function splStack(array $values): SplStack
    {
        /** @var SplStack<TValue> $stack */
        $stack = new SplStack();

        foreach ($values as $value) {
            $stack->push($value);
        }

        return $stack;
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return SplQueue<TValue>
     */
    public static function splQueue(array $values): SplQueue
    {
        /** @var SplQueue<TValue> $queue */
        $queue = new SplQueue();

        foreach ($values as $value) {
            $queue->enqueue($value);
        }

        return $queue;
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return SplFixedArray<TValue>
     */
    public static function splFixedArray(array $values): SplFixedArray
    {
        return SplFixedArray::fromArray($values);
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return SplMinHeap<TValue>
     */
    public static function splMinHeap(array $values): SplMinHeap
    {
        /** @var SplMinHeap<TValue> $heap */
        $heap = new SplMinHeap();

        foreach ($values as $value) {
            $heap->insert($value);
        }

        return $heap;
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return SplMaxHeap<TValue>
     */
    public static function splMaxHeap(array $values): SplMaxHeap
    {
        /** @var SplMaxHeap<TValue> $heap */
        $heap = new SplMaxHeap();

        foreach ($values as $value) {
            $heap->insert($value);
        }

        return $heap;
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return Vector<TValue>
     */
    public static function dsVector(array $values): Vector
    {
        return new Vector($values);
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return Deque<TValue>
     */
    public static function dsDeque(array $values): Deque
    {
        return new Deque($values);
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return Set<TValue>
     */
    public static function dsSet(array $values): Set
    {
        return new Set($values);
    }

    /**
     * A `Ds\Map` accepts keys of any type, but only an array key can come from
     * a mapped source, so the template is bound accordingly. Mapping to a map
     * keyed by anything else falls back to the class's own constructor.
     *
     * @template TKey of array-key
     * @template TValue
     * @param array<TKey, TValue> $values
     * @return Map<TKey, TValue>
     */
    public static function dsMap(array $values): Map
    {
        return new Map($values);
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return Stack<TValue>
     */
    public static function dsStack(array $values): Stack
    {
        return new Stack($values);
    }

    /**
     * @template TValue
     * @param list<TValue> $values
     * @return Queue<TValue>
     */
    public static function dsQueue(array $values): Queue
    {
        return new Queue($values);
    }

    /**
     * @template TKey
     * @template TValue
     * @param TKey $key
     * @param TValue $value
     * @return Pair<TKey, TValue>
     */
    public static function dsPair(mixed $key, mixed $value): Pair
    {
        return new Pair($key, $value);
    }
}
