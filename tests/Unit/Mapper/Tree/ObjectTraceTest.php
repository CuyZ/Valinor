<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree;

use CuyZ\Valinor\Mapper\Tree\ObjectTrace;
use CuyZ\Valinor\Type\Types\NativeClassType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ObjectTraceTest extends TestCase
{
    public function test_detects_circular_dependency_in_two_iterations_only(): void
    {
        $trace = new ObjectTrace();
        $type = new NativeClassType(stdClass::class);

        $trace = $trace->markAsVisited($type);

        self::assertFalse($trace->hasDetectedCircularDependency($type));

        $trace = $trace->markAsVisited($type);

        self::assertTrue($trace->hasDetectedCircularDependency($type));
    }
}
