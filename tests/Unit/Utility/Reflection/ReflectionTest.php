<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

use CuyZ\Valinor\Tests\Fake\FakeReflector;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithPropertyWithNativeIntersectionType;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithPropertyWithNativeUnionType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use Reflector;
use RuntimeException;
use stdClass;

use function get_class;

final class ReflectionTest extends TestCase
{
    public function test_get_class_reflection_returns_class_reflection(): void
    {
        $className = stdClass::class;
        $classReflection = Reflection::class($className);

        self::assertSame($className, $classReflection->getName());
    }

    public function test_class_reflection_is_created_only_once(): void
    {
        $className = stdClass::class;
        $classReflectionA = Reflection::class($className);
        $classReflectionB = Reflection::class($className);

        self::assertSame($classReflectionA, $classReflectionB);
    }

    public function test_function_reflection_is_created_only_once(): void
    {
        $function = fn () => 42;

        $functionReflectionA = Reflection::function($function);
        $functionReflectionB = Reflection::function($function);

        self::assertSame($functionReflectionA, $functionReflectionB);
    }

    public function test_reflection_signatures_are_correct(): void
    {
        $class = get_class(new class () {
            public string $property;

            public function method(string $parameter): void
            {
            }
        });

        $reflectionClass = new ReflectionClass($class);
        $reflectionProperty = $reflectionClass->getProperty('property');
        $reflectionMethod = $reflectionClass->getMethod('method');
        $reflectionParameter = $reflectionMethod->getParameters()[0];

        self::assertSame($class, Reflection::signature($reflectionClass));
        self::assertSame($class . '::$property', Reflection::signature($reflectionProperty));
        self::assertSame($class . '::method()', Reflection::signature($reflectionMethod));
        self::assertSame($class . '::method($parameter)', Reflection::signature($reflectionParameter));
    }

    public function test_invalid_reflection_signature_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid reflection type `' . FakeReflector::class . '`.');

        $wrongReflector = new FakeReflector();

        Reflection::signature($wrongReflector);
    }

    public function test_scalar_type_is_handled(): void
    {
        $object = new class () {
            public string $someProperty;
        };

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($object, 'someProperty'))->getType();

        self::assertSame('string', Reflection::flattenType($type));
    }

    public function test_nullable_scalar_type_is_handled(): void
    {
        $object = new class () {
            public ?string $someProperty;
        };

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($object, 'someProperty'))->getType();

        self::assertSame('string|null', Reflection::flattenType($type));
    }

    /**
     * @requires PHP >= 8
     */
    public function test_union_type_is_handled(): void
    {
        $class = ObjectWithPropertyWithNativeUnionType::class;

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($class, 'someProperty'))->getType();

        self::assertSame('int|float', Reflection::flattenType($type));
    }

    /**
     * @requires PHP >= 8
     */
    public function test_mixed_type_is_handled(): void
    {
        $object = new class () {
            public mixed $someProperty;
        };

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($object, 'someProperty'))->getType();
        self::assertSame('mixed', Reflection::flattenType($type));
    }

    /**
     * @requires PHP >= 8.1
     */
    public function test_intersection_type_is_handled(): void
    {
        $class = ObjectWithPropertyWithNativeIntersectionType::class;

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($class, 'someProperty'))->getType();

        self::assertSame('Countable&Iterator', Reflection::flattenType($type));
    }

    /**
     * @param non-empty-string $expectedType
     * @dataProvider callables_with_docblock_typed_return_type
     */
    public function test_docblock_return_type_is_fetched_correctly(
        callable $dockblockTypedCallable,
        string $expectedType
    ): void {
        $type = Reflection::docBlockReturnType(new ReflectionFunction($dockblockTypedCallable));

        self::assertSame($expectedType, $type);
    }

    public function test_docblock_return_type_with_no_docblock_returns_null(): void
    {
        $callable = static function (): void {
        };

        $type = Reflection::docBlockReturnType(new ReflectionFunction($callable));

        self::assertNull($type);
    }

    /**
     * @param non-empty-string $expectedType
     * @dataProvider objects_with_docblock_typed_properties
     */
    public function test_docblock_var_type_is_fetched_correctly(
        Reflector $property,
        string $expectedType
    ): void {
        self::assertEquals($expectedType, Reflection::docBlockType($property));
    }

    public function callables_with_docblock_typed_return_type(): iterable
    {
        yield 'phpdoc' => [
            /**
             * @return int
             */
            static function () {
                return 42;
            },
            'int',
        ];

        yield 'psalm' => [
            /**
             * @psalm-return int
             */
            static function () {
                return 42;
            },
            'int',
        ];

        yield 'phpstan' => [
            /**
             * @phpstan-return int
             */
            static function () {
                return 42;
            },
            'int',
        ];
    }

    public function objects_with_docblock_typed_properties(): iterable
    {
        yield 'phpdoc @var' => [
            new ReflectionProperty(new class () {
                /**
                 * @var string
                 */
                public $foo;
            }, 'foo'),
            'string',
        ];

        yield 'psalm @var standalone' => [
            new ReflectionProperty(new class () {
                /**
                 * @psalm-var string
                 */
                public $foo;
            }, 'foo'),
            'string',
        ];

        yield 'psalm @var leading' => [
            new ReflectionProperty(new class () {
                /**
                 * @psalm-var non-empty-string
                 * @var string
                 */
                public $foo;
            }, 'foo'),
            'non-empty-string',
        ];

        yield 'psalm @var trailing' => [
            new ReflectionProperty(new class () {
                /**
                 * @var string
                 * @psalm-var non-empty-string
                 */
                public $foo;
            }, 'foo'),
            'non-empty-string',
        ];

        yield 'phpstan @var standalone' => [
            new ReflectionProperty(new class () {
                /**
                 * @phpstan-var string
                 */
                public $foo;
            }, 'foo'),
            'string',
        ];

        yield 'phpstan @var leading' => [
            new ReflectionProperty(new class () {
                /**
                 * @phpstan-var non-empty-string
                 * @var string
                 */
                public $foo;
            }, 'foo'),
            'non-empty-string',
        ];

        yield 'phpstan @var trailing' => [
            new ReflectionProperty(new class () {
                /**
                 * @var string
                 * @phpstan-var non-empty-string
                 */
                public $foo;
            }, 'foo'),
            'non-empty-string',
        ];

        yield 'phpdoc @param' => [
            new ReflectionParameter(
                /** @param string $string */
                static function ($string): void {
                },
                'string',
            ),
            'string',
        ];

        yield 'psalm @param standalone' => [
            new ReflectionParameter(
                /** @psalm-param string $string */
                static function ($string): void {
                },
                'string',
            ),
            'string',
        ];

        yield 'psalm @param leading' => [
            new ReflectionParameter(
                /**
                 * @psalm-param non-empty-string $string
                 * @param string $string
                 */
                static function ($string): void {
                },
                'string',
            ),
            'non-empty-string',
        ];

        yield 'psalm @param trailing' => [
            new ReflectionParameter(
                /**
                 * @param string $string
                 * @psalm-param non-empty-string $string
                 */
                static function ($string): void {
                },
                'string',
            ),
            'non-empty-string',
        ];

        yield 'phpstan @param standalone' => [
            new ReflectionParameter(
                /** @phpstan-param string $string */
                static function ($string): void {
                },
                'string',
            ),
            'string',
        ];

        yield 'phpstan @param leading' => [
            new ReflectionParameter(
                /**
                 * @phpstan-param non-empty-string $string
                 * @param string $string
                 */
                static function ($string): void {
                },
                'string',
            ),
            'non-empty-string',
        ];

        yield 'phpstan @param trailing' => [
            new ReflectionParameter(
                /**
                 * @param string $string
                 * @phpstan-param non-empty-string $string
                 */
                static function ($string): void {
                },
                'string',
            ),
            'non-empty-string',
        ];
    }
}
