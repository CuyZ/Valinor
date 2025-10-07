<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class MethodObjectBuilderTest extends TestCase
{
    public function test_signature_is_method_signature(): void
    {
        $class = (new class () {
            public static function someMethod(): stdClass
            {
                return new stdClass();
            }
        })::class;

        $objectBuilder = new MethodObjectBuilder($class, 'someMethod', new Parameters());

        self::assertSame("$class::someMethod()", $objectBuilder->signature());
    }

    public function test_exception_thrown_by_method_is_caught_and_wrapped(): void
    {
        $class = (new class () {
            public static function someMethod(): stdClass
            {
                throw new RuntimeException('some exception', 1337);
            }
        })::class;

        $objectBuilder = new MethodObjectBuilder($class, 'someMethod', new Parameters());

        $this->expectException(UserlandError::class);

        $objectBuilder->buildObject([]);
    }

    public function test_arguments_instance_stays_the_same(): void
    {
        $class = (new class () {
            public static function someMethod(): stdClass
            {
                return new stdClass();
            }
        })::class;

        $objectBuilder = new MethodObjectBuilder($class, 'someMethod', new Parameters());

        $argumentsA = $objectBuilder->describeArguments();
        $argumentsB = $objectBuilder->describeArguments();

        self::assertSame($argumentsA, $argumentsB);
    }
}
