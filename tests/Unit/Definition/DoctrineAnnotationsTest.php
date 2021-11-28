<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\DoctrineAnnotations;
use CuyZ\Valinor\Definition\Exception\InvalidReflectionParameter;
use CuyZ\Valinor\Tests\Fixture\Annotation\AnnotationWithArguments;
use CuyZ\Valinor\Tests\Fixture\Annotation\BasicAnnotation;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

use function get_class;
use function iterator_to_array;

final class DoctrineAnnotationsTest extends TestCase
{
    public function test_empty_annotations_returns_empty_results(): void
    {
        $object = new class () {
            public string $property;

            public function method(string $parameter): void
            {
            }
        };

        $reflections = [
            new ReflectionClass($object),
            new ReflectionProperty($object, 'property'),
            new ReflectionMethod($object, 'method'),
            new ReflectionParameter([$object, 'method'], 'parameter'),
        ];

        foreach ($reflections as $reflection) {
            $annotations = new DoctrineAnnotations($reflection);

            self::assertEmpty(iterator_to_array($annotations));
            self::assertCount(0, $annotations);
            self::assertFalse($annotations->has(BasicAttribute::class));
            self::assertEmpty($annotations->ofType(BasicAttribute::class));
        }
    }

    public function test_class_annotations_are_fetched_correctly(): void
    {
        $object =
            /**
             * @BasicAnnotation
             * @AnnotationWithArguments(foo="foo")
             */
            new class () { };

        $annotations = new DoctrineAnnotations(new ReflectionClass($object));

        self::assertCount(2, $annotations);
        self::assertTrue($annotations->has(BasicAnnotation::class));
        self::assertTrue($annotations->has(AnnotationWithArguments::class));
        self::assertCount(1, $annotations->ofType(BasicAnnotation::class));
        self::assertCount(1, $annotations->ofType(AnnotationWithArguments::class));
    }

    public function test_property_annotations_are_fetched_correctly(): void
    {
        $object = new class () {
            /**
             * @BasicAnnotation
             * @AnnotationWithArguments(foo="foo")
             */
            public string $property;
        };

        $annotations = new DoctrineAnnotations(new ReflectionProperty($object, 'property'));

        self::assertCount(2, $annotations);
        self::assertTrue($annotations->has(BasicAnnotation::class));
        self::assertTrue($annotations->has(AnnotationWithArguments::class));
        self::assertCount(1, $annotations->ofType(BasicAnnotation::class));
        self::assertCount(1, $annotations->ofType(AnnotationWithArguments::class));
    }

    public function test_method_annotations_are_fetched_correctly(): void
    {
        $object = new class () {
            /**
             * @BasicAnnotation
             * @AnnotationWithArguments(foo="foo")
             */
            public function method(): void
            {
            }
        };

        $annotations = new DoctrineAnnotations(new ReflectionMethod($object, 'method'));

        self::assertCount(2, $annotations);
        self::assertTrue($annotations->has(BasicAnnotation::class));
        self::assertTrue($annotations->has(AnnotationWithArguments::class));
        self::assertCount(1, $annotations->ofType(BasicAnnotation::class));
        self::assertCount(1, $annotations->ofType(AnnotationWithArguments::class));
    }

    public function test_parameter_annotations_returns_empty_array(): void
    {
        $object = new class () {
            public function method(string $parameter): void
            {
            }
        };

        $reflection = (new ReflectionParameter([$object, 'method'], 'parameter'));

        self::assertEmpty(new DoctrineAnnotations($reflection));
    }

    public function test_invalid_reflection_throws_exception(): void
    {
        $wrongReflector = new class () implements Reflector {
            public static function export(): string
            {
                return 'foo';
            }

            public function __toString(): string
            {
                return 'foo';
            }
        };

        $this->expectException(InvalidReflectionParameter::class);
        $this->expectExceptionCode(1534263918);
        $this->expectExceptionMessage('Invalid parameter given (type `' . get_class($wrongReflector) . '`), it must be an instance of `' . ReflectionClass::class . '`, `' . ReflectionProperty::class . '`, `' . ReflectionMethod::class . '`.');

        new DoctrineAnnotations($wrongReflector);
    }
}
