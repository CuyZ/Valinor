<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Priority;

use CuyZ\Valinor\Tests\Traits\IteratorTester;
use CuyZ\Valinor\Utility\Priority\HasPriority;
use CuyZ\Valinor\Utility\Priority\PrioritizedList;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_reverse;
use function array_values;

final class PrioritizedListTest extends TestCase
{
    use IteratorTester;

    public function test_objects_are_sorted_by_priority(): void
    {
        $objects = [
            42 => $this->prioritizedObject(42),
            0 => new stdClass(),
            -42 => $this->prioritizedObject(-42),
        ];

        $reversedObjects = array_reverse(array_values($objects));
        $prioritizedList = new PrioritizedList(...$reversedObjects);

        $this->checkIterable($prioritizedList, $objects);
    }

    private function prioritizedObject(int $priority): HasPriority
    {
        return new class ($priority) implements HasPriority {
            public function __construct(private int $priority) {}

            public function priority(): int
            {
                return $this->priority;
            }
        };
    }
}
